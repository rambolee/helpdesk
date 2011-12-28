<?php

/**
 * This is the model class for table "mail_attachment".
 *
 * The followings are the available columns in table 'mail_attachment':
 * @property integer $id
 * @property integer $mail_id
 * @property string $path
 * @property string $file_name
 * @property string $file_type
 * @property string $file_description
 */
class mail_attachment extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return mail_attachment the static model class
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
		return 'mail_attachment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('mail_id', 'required'),
			array('mail_id', 'numerical', 'integerOnly'=>true),
			array('file_name, file_type', 'length', 'max'=>255),
			array('path, file_description', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, mail_id, path, file_name, file_type, file_description', 'safe', 'on'=>'search'),
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
			'mail_id' => 'Mail',
			'path' => 'Path',
			'file_name' => 'File Name',
			'file_type' => 'File Type',
			'file_description' => 'File Description',
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

		$criteria->compare('mail_id',$this->mail_id);

		$criteria->compare('path',$this->path,true);

		$criteria->compare('file_name',$this->file_name,true);

		$criteria->compare('file_type',$this->file_type,true);

		$criteria->compare('file_description',$this->file_description,true);

		return new CActiveDataProvider('mail_attachment', array(
			'criteria'=>$criteria,
		));
	}
}