<?php

/**
 * This is the model class for table "report_content".
 *
 * The followings are the available columns in table 'report_content':
 * @property integer $id
 * @property string $list_mask
 * @property string $creator
 * @property string $creator_display
 * @property string $create_time
 * @property string $last_update_time
 * @property integer $status
 * @property integer $kind
 * @property integer $module
 * @property integer $emergency
 * @property string $tags
 */
class report_content extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return report_content the static model class
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
		return 'report_content';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('create_time, last_update_time, status', 'required'),
			array('status, kind, module, emergency', 'numerical', 'integerOnly'=>true),
			array('list_mask, creator', 'length', 'max'=>255),
			array('creator_display, tags', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, list_mask, creator, creator_display, create_time, last_update_time, status, kind, module, emergency, tags', 'safe', 'on'=>'search'),
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
			'creator' => 'Creator',
			'creator_display' => 'Creator Display',
			'create_time' => 'Create Time',
			'last_update_time' => 'Last Update Time',
			'status' => 'Status',
			'kind' => 'Kind',
			'module' => 'Module',
			'emergency' => 'Emergency Level',
			'tags' => 'Tags',
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

		$criteria->compare('creator',$this->creator,true);

		$criteria->compare('creator_display',$this->creator_display,true);

		$criteria->compare('create_time',$this->create_time,true);

		$criteria->compare('last_update_time',$this->last_update_time,true);

		$criteria->compare('status',$this->status);

		$criteria->compare('kind',$this->kind);

		$criteria->compare('module',$this->module);

		$criteria->compare('emergency',$this->emergency);

		$criteria->compare('tags',$this->tags,true);

		return new CActiveDataProvider('report_content', array(
			'criteria'=>$criteria,
		));
	}
}
