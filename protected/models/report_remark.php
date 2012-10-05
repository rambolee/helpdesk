<?php

/**
 * This is the model class for table "report_remark".
 *
 * The followings are the available columns in table 'report_remark':
 * @property integer $id
 * @property string $list_id
 * @property string $time
 * @property integer $operator_id
 * @property string $operator_name
 * @property string $operator_display_name
 * @property string $content
 */
class report_remark extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return report_remark the static model class
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
		return 'report_remark';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('list_id, time, operator_id, content', 'required'),
			array('operator_id', 'numerical', 'integerOnly'=>true),
			array('list_id, operator_name, operator_display_name, content', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, list_id, time, operator_id, operator_name, operator_display_name, content', 'safe', 'on'=>'search'),
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
			'list_id' => 'List',
			'time' => 'Time',
			'operator_id' => 'Operator',
			'operator_name' => 'Operator Name',
			'operator_display_name' => 'Operator Display Name',
			'content' => 'Content',
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

		$criteria->compare('list_id',$this->list_id,true);

		$criteria->compare('time',$this->time,true);

		$criteria->compare('operator_id',$this->operator_id);

		$criteria->compare('operator_name',$this->operator_name,true);

		$criteria->compare('operator_display_name',$this->operator_display_name,true);

		$criteria->compare('content',$this->content,true);

		return new CActiveDataProvider('report_remark', array(
			'criteria'=>$criteria,
		));
	}
}