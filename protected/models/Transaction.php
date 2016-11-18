<?php

/**
 * This is the model class for table "transaction".
 *
 * The followings are the available columns in table 'transaction':
 * @property integer $id_transaction
 * @property string $code
 * @property string $date_order
 * @property string $date_confirmation
 * @property string $date_verification
 * @property integer $customer_id
 * @property integer $confirmation_id
 * @property integer $verification_id
 * @property integer $payment_method
 * @property integer $payment_total
 * @property string $payment_file
 * @property integer $shipping_type
 * @property integer $shipping_brand
 * @property integer $shipping_code
 * @property integer $status
 */
class Transaction extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'transaction';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('code, date_order, customer_id, confirmation_id, verification_id, payment_method, payment_total, payment_file, shipping_type, shipping_brand, shipping_code, status', 'required','on'=>'create'),
			array('confirmation_id, payment_file, date_confirmation', 'required','on'=>'confirmation'),
			array('payment_method, payment_total, date_order', 'required','on'=>'checkout'),
			array('customer_id, confirmation_id, verification_id, payment_method, payment_total, shipping_type, shipping_brand, shipping_code, status', 'numerical', 'integerOnly'=>true),
			array('code', 'length', 'max'=>25),
			array('payment_file', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_transaction, code, date_order, date_confirmation, date_verification, customer_id, confirmation_id, verification_id, payment_method, payment_total, payment_file, shipping_type, shipping_brand, shipping_code, status', 'safe', 'on'=>'search'),
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
			'Customer'=>array(self::BELONGS_TO,'User','customer_id'),
			'Confirmation'=>array(self::BELONGS_TO,'User','confirmation_id'),
			'Verification'=>array(self::BELONGS_TO,'User','verification_id'),
			);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_transaction' => 'Id Transaction',
			'code' => 'Code',
			'date_order' => 'Tanggal Order',
			'date_confirmation' => 'Tanggal Konfirmasi Pembayaran',
			'date_verification' => 'Tanggal Verifikasi Transaksi',
			'customer_id' => 'Pelanggan',
			'confirmation_id' => 'Dikonfirmasi Oleh',
			'verification_id' => 'Diverifikasi Oleh',
			'payment_method' => 'Metode Pembayaran',
			'payment_total' => 'Total Tagihan',
			'payment_file' => 'Bukti Transfer',
			'shipping_type' => 'Metode Pengiriman',
			'shipping_brand' => 'Kurir Pengiriman',
			'shipping_code' => 'NO Resi Pengiriman',
			'status' => 'Status Transaksi',
			);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id_transaction',$this->id_transaction);
		$criteria->compare('code',$this->code,true);
		$criteria->compare('date_order',$this->date_order,true);
		$criteria->compare('date_confirmation',$this->date_confirmation,true);
		$criteria->compare('date_verification',$this->date_verification,true);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('confirmation_id',$this->confirmation_id);
		$criteria->compare('verification_id',$this->verification_id);
		$criteria->compare('payment_method',$this->payment_method);
		$criteria->compare('payment_total',$this->payment_total);
		$criteria->compare('payment_file',$this->payment_file,true);
		$criteria->compare('shipping_type',$this->shipping_type);
		$criteria->compare('shipping_brand',$this->shipping_brand);
		$criteria->compare('shipping_code',$this->shipping_code);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Transaction the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function total(){
		$criteria= new CDbCriteria();
		$criteria->distinct = true;
		$criteria->group = 'product_id';
		$criteria->order = 'product_id';
		$criteria->condition = 'status=0 AND customer_id='.YII::app()->user->id;
		$totalBeli=new CActiveDataProvider('Order', array(
			'criteria'=>$criteria,
			'pagination'=>false,
			));

		$beli =  $totalBeli->totalItemCount;
		$total=0;
		for ($i=0; $i < $beli; $i++) { 
			$total = Yii::app()->db->createCommand('
				SELECT SUM(orders.quantity*product.price) as Jumlah FROM transaction_detail as orders LEFT JOIN product ON orders.product_id=product.id_product WHERE orders.status=0 AND orders.customer_id='.YII::app()->user->id.'
				')->queryScalar();
		}

		if($total==null){
			return "0";
		}else{
			return $total;
		}

	}

	public function grandtotal($data){
		$criteria= new CDbCriteria();
		$criteria->distinct = true;
		$criteria->group = 'product_id';
		$criteria->order = 'product_id';
		$criteria->condition = 'transaction_id='.$data;
		$totalBeli=new CActiveDataProvider('Order', array(
			'criteria'=>$criteria,
			'pagination'=>false,
			));

		$beli =  $totalBeli->totalItemCount;
		$total=0;
		for ($i=0; $i < $beli; $i++) { 
			$total = Yii::app()->db->createCommand('
				SELECT SUM(orders.quantity*product.price) as Jumlah FROM transaction_detail as orders LEFT JOIN product ON orders.product_id=product.id_product WHERE orders.transaction_id='.$data.'
				')->queryScalar();
		}

		if($total==null){
			return "0";
		}else{
			return $total;
		}

	}	

	public function status($data){
		if($data==0){
			return "Belum di Transfer";
		}else if($data==1){
			return "Telah di Transfer";
		}else if($data==2){
			return "Sudah di Verifikasi";
		}else if($data==3){
			return "Proses Pengiriman Barang";
		}else if($data==4){
			return "Barang di Terima";			
		}else{
			return "Tidak di Ketahui";
		}
	}

	public function method($data){
		if($data==1){
			return "Bank BCA";
		}else if($data==2){
			return "Bank Mandiri";
		}else{
			return "Bank BNI";
		}
	}	
}
