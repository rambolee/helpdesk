<?php

/**
 * This is the model class for table "report_log".
 *
 * The followings are the available columns in table 'report_log':
 * @property integer $id
 * @property string $list_mask
 * @property string $time
 * @property integer $operator_id
 * @property string $operator_name
 * @property string $operator_display_name
 * @property string $action
 * @property string $remark
 * @property string $result
 */
class report_log extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return report_log the static model class
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
		return 'report_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('time, operator_id, operator_name, operator_display_name, action, remark, result', 'required'),
			array('operator_id', 'numerical', 'integerOnly'=>true),
			array('list_mask, operator_name, operator_display_name, action', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, list_mask, time, operator_id, operator_name, operator_display_name, action, remark, result', 'safe', 'on'=>'search'),
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
			'time' => 'Time',
			'operator_id' => 'Operator',
			'operator_name' => 'Operator Name',
			'operator_display_name' => 'Operator Display Name',
			'action' => 'Action',
			'remark' => 'Remark',
			'result' => 'Result',
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

		$criteria->compare('time',$this->time,true);

		$criteria->compare('operator_id',$this->operator_id);

		$criteria->compare('operator_name',$this->operator_name,true);

		$criteria->compare('operator_display_name',$this->operator_display_name,true);

		$criteria->compare('action',$this->action,true);

		$criteria->compare('remark',$this->remark,true);

		$criteria->compare('result',$this->result,true);

		return new CActiveDataProvider('report_log', array(
			'criteria'=>$criteria,
		));
	}
}