<?php

/**
 * This is the model class for table "mail_data".
 *
 * The followings are the available columns in table 'mail_data':
 * @property integer $id
 * @property string $list_mask
 * @property string $create_time
 * @property string $mail_header
 * @property string $mail_from
 * @property string $mail_to
 * @property string $mail_cc
 * @property string $title
 * @property string $content_text
 * @property string $content_html
 * @property string $file_name
 * @property integer $attachment
 */
class mail_data extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return mail_data the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'mail_data';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('create_time, title', 'required'),
			array('attachment', 'numerical', 'integerOnly'=>true),
			array('list_mask, mail_from, mail_to, title, file_name', 'length', 'max'=>255),
			array('mail_header, content_text, content_html', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, list_mask, create_time, mail_header, mail_from, mail_to, mail_cc, title, content_text, content_html, file_name, attachment', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'list_mask' => 'List Mask',
			'create_time' => 'Create Time',
			'mail_header' => 'Mail Header',
			'mail_from' => 'Mail From',
			'mail_to' => 'Mail To',
			'mail_cc' => 'Mail Cc',
			'title' => 'Title',
			'content_text' => 'Content Text',
			'content_html' => 'Content Html',
			'file_name' => 'File Name',
			'attachment' => 'Attachment',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);

		$criteria->compare('list_mask',$this->list_mask,true);

		$criteria->compare('create_time',$this->create_time,true);

		$criteria->compare('mail_header',$this->mail_header,true);

		$criteria->compare('mail_from',$this->mail_from,true);

		$criteria->compare('mail_to',$this->mail_to,true);

		$criteria->compare('mail_cc',$this->mail_cc,true);

		$criteria->compare('title',$this->title,true);

		$criteria->compare('content_text',$this->content_text,true);

		$criteria->compare('content_html',$this->content_html,true);

		$criteria->compare('file_name',$this->file_name,true);

		$criteria->compare('attachment',$this->attachment);

		return new CActiveDataProvider('mail_data', array(
			'criteria'=>$criteria,
		));
	}
}
