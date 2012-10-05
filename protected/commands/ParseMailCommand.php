<?php
@ini_set("memory_limit","1000M"); 
@ini_set("max_execution_time", "1800");
require_once("../lib/mail_tools/parse_mail/v2/rfc822_addresses.php") ;
require_once("../lib/mail_tools/parse_mail/v2/mime_parser.php") ;
#require_once("./config/mail_config.php") ;

define('NOT_HAVE_ATTATCHMENT', 0) ;
define('HAVE_ATTATCHMENT', 1) ;

// emergency
define('UNDEAL', 1);
define('NORMAL_LEVEL',3) ;

// mail_data type
define('MAIN_MAIL', 1) ;   
define('MERGED_MAIL', 2) ; 
define('REPLAY_MAIL', 3) ; 
define('NOTICE_INFO', 4) ; 

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
					// subject 取值要放在Mask计算前面，Mask可能会更改subject
					$strSubject     = $objParser->getSubject() ; 
					$strOrigSubject = $strSubject ;
					$bHaveMask		= true ;
					$strListMask    = self::_buildMailListId($strSubject, $bHaveMask) ;
					$bHaveAttachment = NOT_HAVE_ATTATCHMENT ;

					// save mail info
					$objMaildata = new mail_data() ;
					$strDate = $objParser->getHeader('date') ;
					if( !empty($strDate) ){
						$nDate = strtotime($objParser->getHeader('date')) ;
						$objMaildata->create_time = date('Y-m-d H:i:s', $nDate) ; 
					}else{
						$objMaildata->create_time = date('Y-m-d H:i:s') ;
					}
					$objMaildata->list_mask		= $strListMask ;
					$objMaildata->mail_header	= $objParser->getHeader() ; 
					$objMaildata->mail_from		= $objParser->getHeader('from') ;
					$objMaildata->mail_to		= $objParser->getHeader('to') ;
					$objMaildata->mail_cc		= $objParser->getHeader('cc') ;
					$objMaildata->title			= $strSubject ;
					$objMaildata->content_text  = $objParser->getMessageBody('text') ;
					$objMaildata->content_html	= $objParser->getMessageBody('html') ;
					$objMaildata->file_name		= $strFile ;
					if( $bHaveMask ){
						$objMaildata->type 	= REPLAY_MAIL ;
					}else{
						$objMaildata->type	= MAIN_MAIL ;
					}

					$aAttachments	= $objParser->getAttachments() ;
					if( !empty($aAttachments) ){
						$bHaveAttachment = HAVE_ATTATCHMENT ;	
					}
					$objMaildata->attachment	= $bHaveAttachment ;

					if( !$objMaildata->save(true) ){
						$errors = $objMaildata->getErrors() ;
						$msg = array() ;                                 
						foreach( $errors as $id => $error ){             
							$msg[] = " $id: " . implode( ', ', $error ) ;
						}                                                
						$msg .= implode( '<br />',  $msg ) ; 
						throw new Exception( $msg ) ;
					}else{
						self::_moveMail($strFile) ;

						/** 
						 * create a new report_content
						 * 如果是REPLAY_MAIL 则无需创建 report_content 
						 * 只有MAIN_MAIL才创建report_content
						 */
						if( $objMaildata->type == MAIN_MAIL ){
							$objContent 				= new report_content() ;
							$objContent->list_mask 		= $strListMask ;
							$objContent->title			= $strOrigSubject ;
							$objContent->creator		= self::_getMailAddr($objMaildata->mail_from) ;
							$objContent->creator_mail	= $objMaildata->mail_from ;
							$objContent->create_time 	= $objMaildata->create_time ;
							$objContent->last_update_time 	= date('Y-m-d H:i:s') ;
							$objContent->status			= UNDEAL ; // 未处理
							$objContent->emergency		= NORMAL_LEVEL ; // 一般

							if( !$objContent->save(true) ){
								$errors = $objContent->getErrors() ;
								$msg = array() ;
								foreach($errors as $id=> $error){
									$msg[] = "$id:" . implode(', ',  $error) ;
								}
								$msg .= implode( '<br/>', $msg) ;
								throw new Exception($msg) ;
							}

							// 写日志
							self::_addLog($strListMask, $objContent->create_time, $objContent->creator, '创建CASE成功', NULL, NULL) ;
						}else{
							// 写日志
							self::_addLog($strListMask, $objContent->create_time, $objContent->creator, '追加CASE成功', NULL, NULL) ;
						}
					}
					
					// save mail attachment
					if( $bHaveAttachment ){
						foreach( $aAttachments as $aFile ){
							$objAttatchment				= new mail_attachment() ;		
							$objAttatchment->mail_id	= $objMaildata->id ;
							$objAttatchment->path		= $aFile['attachment_path'] ;
							$objAttatchment->file_name	= $aFile['file_name'] ;
							$objAttatchment->file_type  = $aFile['file_type'] ;
							$objAttatchment->file_description = $aFile['file_description'] ;
							$objAttatchment->file_id	= $aFile['file_id'] ;

							if( !$objAttatchment->save(true) ){
								$errors = $objAttatchment->getErrors() ;
								$msg = array() ;                                 
								foreach( $errors as $id => $error ){             
									$msg[] = " $id: " . implode( ', ', $error ) ;
								}                                                
								$msg .= implode( '<br />',  $msg ) ; 
								throw new Exception( $msg ) ;
							}else{
								//save attachment file
								$strOrigFile = "{$objAttatchment->path}/{$objAttatchment->mail_id}_{$objAttatchment->file_name}" ;
								$nPos        = stripos(strtolower($objAttatchment->file_description), 'utf-8') ;
								if( $nPos === false ){
									$strFile = @mb_convert_encoding($strOrigFile, 'utf-8', 'gbk') ;
								}else{
									$strFile = $strOrigFile ; 
								}
								$handle		 = fopen($strFile, 'wb') ;
								fwrite($handle, $aFile['file_body']) ;
								fclose($handle) ;
							}
						}	
					}
				}else{
					self::_moveMail($strFile) ;
				}
			}
		}
	}

	private static function _addLog($list_mask='', $time = NULL, $operator_name = NULL, $action = "", $remark="", $result = ""){
		$time = empty($time) ? date('Y-m-d H:i:s') : $time ;
		$strSql = "insert into `report_log` values (null, '{$list_mask}', '{$time}', '', '{$operator_name}', '', '{$action}', '{$remark}', '{$result}')";
		Yii::app()->db->createCommand($strSql)->execute() ;	
	}

	/**
	 * get real mail address from such string as rms<rms@baidu.com>
	 */
	private static function _getMailAddr($strMail){
		// rms<rms@baidu.com>
		$aMatch 	= array();
		$strEmail 	= '' ;
		$nPos = preg_match("/<[a-z]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?>/i", $strMail, $aMatch) ;	
		if( $nPos > 0 ){                                             
			// 获取 rms@baidu.com                                    
			$strEmail = substr($aMatch[0], 1, strlen($aMatch[0])-2) ;
		}

		return $strEmail ;
	}

	private static function _moveMail($strFile, $strDest = MAIL_ARCHIVED_DIR){
		// move mail file to archived directory
		$strIncomingFile = MAIL_DIR_PATH . MAIL_INCOMING_DIR . '/' . $strFile ;
		$strArchiveFile  = MailParser::generateHashDir( $strDest ) . '/' . $strFile ;
		system("mv " . $strIncomingFile . " " . $strArchiveFile) ;	
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
		$aData = isset($aDecodeData['ExtractedAddresses']) ? $aDecodeData['ExtractedAddresses'] : NULL ;
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

	private static function _buildMailListId(&$strSubject, &$bHaveMask){
		// 匹配 LLL-NNNNN-NNN
		$strMask = '' ;
		if( !preg_match("/\[[A-Z]{3}-[1-9]{5}-[1-9]{3}\]/", $strSubject, $strMask) ){
			$strMask    = "[" . self::_generateCaseMask() . "]" ; 
			$strSubject = $strMask . $strSubject ;
			$bHaveMask 	= false ;
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
		$strDirPath = self::generateHashDir() ;

		$this->_aParams = array(
			'File'		=> $strMailFile ,			

			/* Save the message body parts to a directory     */
			//'SaveBody' 	=> $strDirPath ,		
		) ;
		$this->_aDecodeData = NULL ; 
	}

	public static function generateHashDir($strDestDir=MAIL_ARCHIVED_DIR){

		$strMailPath = MAIL_DIR_PATH . $strDestDir ;
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
						if(!isset($aList['name'])){
							$aList['name'] = $aList['address'] ;
						}
						$aTemp[] = "{$aList['name']} <{$aList['address']}>" ;	
					}	
					$strResult .= join( ';', $aTemp ) ;
				}
			  break ;
			case 'date' :
				$strKey = 'date:' ;
				if( isset($this->_aDecodeData['Headers'][$strKey]) && !empty($this->_aDecodeData['Headers'][$strKey]) ){
					$strResult = $this->_aDecodeData['Headers'][$strKey] ;
				}
				break ;
			default :
			  $strResult = print_r($this->_aDecodeData['Headers'], true) ;
		}

		return $strResult ;
	}

	public function getSubject(){

		if( empty($this->_aDecodeData) ) return ;	
		if( isset( $this->_aDecodeData['DecodedHeaders'] ) ){
			$strSubject  = $this->_aDecodeData['DecodedHeaders']['subject:'][0][0]['Value'] ;
			$strEncoding = strtoupper($this->_aDecodeData['DecodedHeaders']['subject:'][0][0]['Encoding']) ;
			if( $strEncoding != 'UTF-8' ){
				$strSubject = mb_convert_encoding( $strSubject, 'UTF-8', $strEncoding ) ;
			}
		}else{
			$strSubject = '' ;
		}
		return $strSubject ;
	}

	public function getMessageBody($strType){

		if( empty($this->_aDecodeData) ) return ;	

		/**
		  * 基本格式：
		  *   [Parts] => Array(),
		  *   [Body]  => '',
		  *   即纯文本邮件，没有html格式
		  */
		if( isset($this->_aDecodeData['Body']) && !empty($this->_aDecodeData['Body']) ){
			$strContentType = strtolower($this->_aDecodeData['Headers']['content-type:']) ;
			$strEncoding    = self::_getCharsetEncoding($strContentType) ;
			$strBody		= $this->_aDecodeData['Body'] ;
			if( !empty($strEncoding) && $strEncoding != 'utf-8' ){
				$strResult = mb_convert_encoding( $strBody, 'utf-8', $strEncoding ) ;  
			}else{
				$strResult = $strBody ;
			}
			return $strResult ;
		}

		$aData = $this->_aDecodeData['Parts'] ;
		/**
		  * 判断是简单格式mail 还是复杂格式mail
		  * 简单格式：
		  *  [Parts] => Array
		  *	  (
		  *		  [0] => Array
		  *		  (
		  *			[Headers] => Array
		  *			  (
		  *				  [content-type:] => text/plain; charset="utf-8"
		  *				  [content-transfer-encoding:] => base64
		  *			  )
		  *
		  * 复杂格式：
		  * [Parts] => Array
		  *	 (
		  *		 [0] => Array
		  *			 (
		  *				 [Headers] => Array
		  *					 (
		  *						 [content-type:] => multipart/alternative;boundary="_000_055701ccb89e608341602189c420com_"
		  *					 )
		  *				 [Parts] => Array
		  *					 (
		  *						 [0] => Array
		  *							 (
	      *								 [Headers] => Array
		  *									 (
		  *										 [content-type:] => text/plain; charset="gb2312"
		  *										 [content-transfer-encoding:] => base64
		  */
		if( empty($aData) ) return ;
		$aData = self::_getMailBodyRoot($aData) ;

		$strResult = '' ;
		$strType   = strtolower( $strType ) ;
		switch($strType){
			case 'text' :
			  // $aData[0]['Headers'][content-type:] => text/plain; charset="utf-8"
			  $strContentType = strtolower($aData[0]['Headers']['content-type:']) ;
			  $strEncoding    = self::_getCharsetEncoding($strContentType) ;
			  $strBody		  = $aData[0]['Body'] ;
			  if( $strEncoding != 'utf-8' ){
					$strResult = mb_convert_encoding( $strBody, 'UTF-8', $strEncoding ) ;  
			  }else{
					$strResult = $strBody ;
			  }
			  break ;
			case 'html' :
			  // $aData[1]['Headers'][content-type:] => text/html; charset="utf-8" 
			  $strContentType = strtolower($aData[1]['Headers']['content-type:']) ;
			  $strEncoding    = self::_getCharsetEncoding($strContentType) ;
			  $strBody		  = $aData[1]['Body'] ;
			  if( $strEncoding != 'utf-8' ){
					$strResult = mb_convert_encoding( $strBody, 'UTF-8', $strEncoding ) ;  
			  }else{
					$strResult = $strBody ;
			  }
			  break ;
			default :
			  // do nothing ;
		}
		return $strResult ;
	}

	private static function _getMailBodyRoot($aData){
		$nPos  = stripos($aData[0]['Headers']['content-type:'], 'text/plain') ;
		if( $nPos === false ){
			$aData = self::_getMailBodyRoot( $aData[0]['Parts'] ) ; 
		}			
		return $aData ;
	}

	private static function _getCharsetEncoding($strEncoding){

		$strResult = '' ;
		if( preg_match('/charset\s*=\s*["|\']([a-zA-Z0-9-\s]+)["|\']/', $strEncoding, $aMatch) ){
			$strResult = $aMatch[1] ;	
		}
		return $strResult ;
	}

	public function getAttachments(){
		//邮件数据为空 或者 是纯文本邮件
		if( empty($this->_aDecodeData) || isset( $this->_aDecodeData['Body'] ) ) return ;	

		$aData = $this->_aDecodeData['Parts'] ;
		$aAttachments = array() ;
		$nPos  = stripos($aData[0]['Headers']['content-type:'], 'multipart/alternative') ;
		if( $nPos !== false ){
			$nIndex = 1 ;
			for( $nIndex ; $nIndex < count($aData) ; $nIndex++ ){
				$aAttachments[] = array(
					'attachment_path'	=> self::generateHashDir( MAIL_ATTACHMENTS_DIR ) ,
					'file_name'			=> isset($aData[$nIndex]['FileName']) ? $aData[$nIndex]['FileName'] : $aData[$nIndex]['Position'] ,
					'file_type'			=> isset($aData[$nIndex]['FileDisposition']) ? $aData[$nIndex]['FileDisposition'] : '' ,
					'file_description'	=> isset($aData[$nIndex]['Headers']['content-disposition:']) ? $aData[$nIndex]['Headers']['content-disposition:'] : $aData[$nIndex]['Headers']['content-type:'] ,
					'file_body'			=> $aData[$nIndex]['Body'] ,
					'file_id' 			=> isset($aData[$nIndex]['Headers']['content-id:']) ? $aData[$nIndex]['Headers']['content-id:'] : '',
				);
			}
		}

		return $aAttachments ;
	}
}

