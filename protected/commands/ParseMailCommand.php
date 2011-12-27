<?php
@ini_set("memory_limit","1000M"); 
@ini_set("max_execution_time", "1800");
require_once("../lib/mail_tools/parse_mail/v2/rfc822_addresses.php") ;
require_once("../lib/mail_tools/parse_mail/v2/mime_parser.php") ;
require_once("./config/mail_config.php") ;

class ParseMailCommand extends CConsoleCommand{

	public function run(){
		$this->_parseMail() ;
	}

	private function _parseMail(){
		//循环解析没封邮件
		$aFiles = self::_getMailFiles() ;
		if( !empty($aFiles) ){
			$strWorkingPath = MAIL_DIR_PATH . MAIL_INCOMING_DIR ;	
			foreach( $aFiles as $strFile ){
				$strFilePath = $strWorkingPath . '/' . $strFile ;
				$objParser   = new MailParser($strFilePath) ;
				$aDecode	 = $objParser->decodeMail() ;
				if( self::_filterMails( $aDecode ) ){
					$strMailSql     = '' ;
					$strTo			= $objParser->getHeader('to') ;
					$strCC			= $objParser->getHeader('cc') ;
					$strFrom		= $objParser->getHeader('from') ;
					$strMailHeader  = $objParser->getHeader() ;
					// subject 取值要放在Mask计算前面，Mask可能会更改subject
					$strSubject		= $objParser->getSubject('subject') ;
					$strListMask    = self::_buildMailListId($strSubject) ;
					$strText		= $objParser->getMessageBody('text') ;
					$strHtml		= $objParser->getMessageBody('html') ;

					$strMailSql = "INSERT INTO mail_data 
						(`id`,`list_mask`,`time`,`mail_header`,`mail_from`,`mail_to`,`mail_cc`,`title`,`content_text`,`content_html`) 
						VALUES('','{$strListMask}',NOW(),'{$strMailHeader}','{$strFrom}','{$strTo}','{$strCC}','{$strSubject}','{$strText}','{$strHtml}')" ;
					$command = Yii::app()->db->createCommand($strMailSql) ;
					$command->execute() ;
					break ;
					//echo $command->getLastInsertId() . "\n";
					//echo $strMailSql . "<br/>" ;
					//$objAttachments	= $objData->getAttachments() ;
				}
			}
		}
	}

	private static function _getMailFiles(){
		//获取目录下的文件列表
		$aFiles = array() ;
		$dh = opendir( MAIL_DIR_PATH . MAIL_INCOMING_DIR ) ;
		while( false !== ( $strFileName = readdir($dh) ) ){
			if( $strFileName != '.' && $strFileName != '..' ){
				$aFiles[] = $strFileName ;
			}	
		}
		sort( $aFiles ) ;
		return $aFiles ;
	}

	private static function _filterMails($aDecodeData){
		//邮件存储规则，并非所有邮件都保存入邮件队列中
		$bPass = false ;
		$aData = $aDecodeData['ExtractedAddresses'] ;
		if( !empty($aData) ){
			$strTo = '' ;
			$strCC = '' ;
			if( isset($aData['to:']) && !empty($aData['to:']) ){
				foreach( $aData['to:'] as $aTo ){
					$strTo .= $aTo['address'] ;	
				}	
			}
			if( isset($aData['cc:']) && !empty($aData['cc:']) ){
				foreach( $aData['cc:'] as $aTo ){
					$strTo .= $aTo['address'] ;	
				}	
			}

			$strFilterString = "{$strTo} | {$strCC} " ;
			$nPos = stripos( $strFilterString, MAIL_FILTER_KEYWORD ) ;
			if( $nPos !== false ){
				$bPass = true ;
			}
		}
		return $bPass ;
	}

	private static function _buildMailListId(&$strSubject){
		// 匹配 LLL-NNNNN-NNN
		$strMask = '' ;
		if( !preg_match("/\[[A-Z]{3}-[1-9]{5}-[1-9]{3}\]/", $strSubject, $strMask) ){
			$strMask    = "[" . self::_generateCaseMask() . "]" ; 
			$strSubject = $strMask . $strSubject ;
		}
		return $strMask ;
	}

	private static function _generateCaseMask($pattern = "LLL-NNNNN-NNN") {
		//计算邮件Mask
		$letters = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
		$numbers = "123456789";

		do {
			$mask = "";
			$bytes = preg_split('//', $pattern, -1, PREG_SPLIT_NO_EMPTY);

			if(is_array($bytes))
			foreach($bytes as $byte) {
				switch(strtoupper($byte)) {
					case 'L':
						$mask .= substr($letters,mt_rand(0,strlen($letters)-1),1);
						break;
					case 'N':
						$mask .= substr($numbers,mt_rand(0,strlen($numbers)-1),1);
						break;
					case 'C': // L or N
						if(mt_rand(0,100) >= 50) { // L
							$mask .= substr($letters,mt_rand(0,strlen($letters)-1),1);
						} else { // N
							$mask .= substr($numbers,mt_rand(0,strlen($numbers)-1),1);
						}
						break;
					case 'Y':
						$mask .= date('Y');
						break;
					case 'M':
						$mask .= date('m');
						break;
					case 'D':
						$mask .= date('d');
						break;
					default:
						$mask .= $byte;
						break;
				}
			}
		} while(NULL != self::_getCaseByMask($mask)) ;
		return $mask;                                                                           
	}                                                                                            

	private static function _getCaseByMask( $strMask ){
	//根据Mask取Case
		$aData  = array() ;
		$strSql = "SELECT * FROM mail_data WHERE `list_mask` = '{$strMask}'" ;
		$aData  = Yii::app()->db->createCommand($strSql)->queryAll() ;
		if( empty( $aData ) ){
			$aData = NULL ;	
		}
		return $aData ;
	}
}

class MailParser{

	private $_mime ;
	private $_aParams ;
	private $_aDecodeData ;

	public function __construct($strMailFile){

		$this->_mime = new mime_parser_class() ;	
		/*
		 * Set to 0 for parsing a single message file
		 * Set to 1 for parsing multiple messages in a single file in the mbox format
		 */
		$this->_mime->mbox = 1 ;

		/*
		 * Set to 0 for not decoding the message bodies
		 */
		$this->_mime->decode_bodies = 1;

		/*
		 * Set to 0 to make syntax errors make the decoding fail
		 */
		$this->_mime->ignore_syntax_errors = 1;

		/*
		 * Set to 0 to avoid keeping track of the lines of the message data
		 */
		$this->_mime->track_lines = 1;

		/*
		 * Set to 1 to make message parts be saved with original file names
		 * when the SaveBody parameter is used.
		 */
		$this->_mime->use_part_file_names = 1;

		//创建邮件保存的Hash散列目录
		$strDirPath = self::_generateHashDir() ;

		$this->_aParams = array(
			'File'		=> $strMailFile ,			

			/* Save the message body parts to a directory     */
			//'SaveBody' 	=> $strDirPath ,		
		) ;
		$this->_aDecodeData = NULL ; 
	}

	private static function _generateHashDir(){

		$strMailPath = MAIL_DIR_PATH . MAIL_ARCHIVED_DIR ;
		$strHashDir  = date("Y") . "_" . date("m") ;
		$strDirPath  = $strMailPath . '/' . $strHashDir ;
		if(!file_exists( $strDirPath )){
			mkdir($strDirPath, 0777) ;	
		}
		return $strDirPath ;
	}

	public function decodeMail($aParams=NULL){

		if( empty($aParams) ){
			$aParams = $this->_aParams ;
		}	
		$aResult = array() ;
		
		if( !$this->_mime->Decode($aParams, $aResult) ){
			echo 'MIME message decoding error: '.$this->_mime->error.' at position '.$this->_mime->error_position;
			if($this->_mime->track_lines
			&& $this->_mime->GetPositionLine($this->_mime->error_position, $line, $column))
				echo ' line '.$line.' column '.$column;
			echo "\n";
		}		
		$this->_aDecodeData = $aResult[0] ;
		return $aResult[0] ;
	}

	public function getHeader($strType=''){

		if( empty($this->_aDecodeData) ) return ;
		$strResult = '' ;
		$strType   = strtolower($strType) ;
		$aData     = $this->_aDecodeData['ExtractedAddresses'] ;
		switch($strType){
			case 'to' :
			case 'cc' :
			case 'from' :
				$strKey = "{$strType}:" ;
				if( isset($aData[$strKey]) && !empty($aData[$strKey]) ){
					$aTemp = array() ;
					foreach( $aData[$strKey] as $aList ){
						$aTemp[] = "{$aList['name']} <{$aList['address']}>" ;	
					}	
					$strResult .= join( ';', $aTemp ) ;
				}
			  break ;
			default :
			  $strResult = print_r($this->_aDecodeData['Headers'], true) ;
		}

		return $strResult ;
	}

	public function getSubject(){

		if( empty($this->_aDecodeData) ) return ;	
		$strSubject  = $this->_aDecodeData['DecodedHeaders']['subject:'][0][0]['Value'] ;
		$strEncoding = strtoupper($this->_aDecodeData['DecodedHeaders']['subject:'][0][0]['Encoding']) ;
		if( $strEncoding != 'UTF-8' ){
			$strSubject = mb_convert_encoding( $strSubject, 'UTF-8', $strEncoding ) ;
		}
		return $strSubject ;
	}

	public function getMessageBody($strType){

		if( empty($this->_aDecodeData) ) return ;	

		$aData = $this->_aDecodeData['Parts'] ;
		if( empty($aData) ) return ;

		$strResult = '' ;
		$strType   = strtolower( $strType ) ;
		switch($strType){
			case 'text' :
			  // $this->_aDecodeData['Parts'][0]['Headers'][content-type:] => text/plain; charset="utf-8"
			  $strContentType = strtolower($aData[0]['Headers']['content-type:']) ;
			  $strEncoding    = self::_getCharsetEncoding($strContentType) ;
			  $strBody		  = $aData[0]['Body'] ;
			  if( $strEncoding != 'utf-8' ){
					$strResult = mb_convert_encoding( $strBody, 'UTF-8', $strContentType ) ;  
			  }else{
					$strResult = $strBody ;
			  }
			  break ;
			case 'html' :
			  // $this->_aDecodeData['Parts'][1]['Headers'][content-type:] => text/html; charset="utf-8" 
			  $strContentType = strtolower($aData[1]['Headers']['content-type:']) ;
			  $strEncoding    = self::_getCharsetEncoding($strContentType) ;
			  $strBody		  = $aData[1]['Body'] ;
			  if( $strEncoding != 'utf-8' ){
					$strResult = mb_convert_encoding( $strBody, 'UTF-8', $strContentType ) ;  
			  }else{
					$strResult = $strBody ;
			  }
			  break ;
			default :
			  // do nothing ;
		}
		return $strResult ;
	}

	private static function _getCharsetEncoding($strEncoding){

		$strResult = '' ;
		if( preg_match('/charset\s*=\s*["|\']([a-zA-Z0-9-\s]+)["|\']/', $strEncoding, $aMatch) ){
			$strResult = $aMatch[1] ;	
		}
		return $strResult ;
	}

	public function getAttachments(){
		if( empty($this->_aDecodeData) ) return ;	
	}
}

