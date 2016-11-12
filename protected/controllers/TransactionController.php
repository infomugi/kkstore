<?php

class TransactionController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			// 'postOnly + delete', // we only allow deletion via POST request
			);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('view','index'),
				'users'=>array('@'),
				'expression'=>'Yii::app()->user->record->level==3',
				),
			array('allow',
				'actions'=>array('create','update','view','delete'),
				'users'=>array('@'),
				'expression'=>'Yii::app()->user->record->level==2',
				),			
			array('allow',
				'actions'=>array('create','update','view','delete','admin','detail','confirmation','verification'),
				'users'=>array('@'),
				'expression'=>'Yii::app()->user->record->level==1',
				),
			array('deny',
				'users'=>array('*'),
				),
			);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
			));
	}

	public function actionDetail($id)
	{
		$this->layout = "front_index";
		$this->render('detail',array(
			'model'=>$this->loadModel($id),
			));
	}	

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$this->layout = "front_page";
		$model=new Transaction;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Transaction']))
		{
			$model->attributes=$_POST['Transaction'];
			$model->code = "T".date('dmY');
			$model->date_order = date('Y-m-d h:i:s');
			$model->customer_id = YII::app()->user->id;
			$model->payment_total = Transaction::model()->total();
			
			//Confirmation & Verification
			$model->verification_id = 0;
			$model->date_verification = NULL;
			$model->confirmation_id = 0;
			$model->date_confirmation = NULL;
			
			//Verification
			$model->status = 0;
			$model->payment_file = 0;
			
			//Shipping
			$model->shipping_type = 0;
			$model->shipping_brand = 0;
			$model->shipping_code = 0;

			if($model->save()){

				$criteria= new CDbCriteria();
				$criteria->distinct = true;
				$criteria->group = 'product_id';
				$criteria->order = 'product_id';
				$criteria->condition = 'customer_id='.YII::app()->user->id;
				$totalBeli=new CActiveDataProvider('Order', array(
					'criteria'=>$criteria,
					'pagination'=>false,
					));

				$beli =  $totalBeli->totalItemCount;

				for ($i=0; $i < $beli; $i++) { 
					$update=Order::model()->findByAttributes(array('customer_id'=>$model->customer_id));
					$update->transaction_id = $model->id_transaction;
					$update->status = 1;
					$update->save();
				}


				$this->redirect(array('detail','id'=>$model->id_transaction));
			}
		}

		$this->render('create',array(
			'model'=>$model,
			));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Transaction']))
		{
			$model->attributes=$_POST['Transaction'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id_transaction));
		}

		$this->render('update',array(
			'model'=>$model,
			));
	}

	public function actionConfirmation($id)
	{
		$this->layout = "front_page";
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Transaction']))
		{
			$model->attributes=$_POST['Transaction'];
			$tmp;
			if(strlen(trim(CUploadedFile::getInstance($model,'payment_file'))) > 0) 
			{ 
				$tmp=CUploadedFile::getInstance($model,'payment_file'); 
				$model->payment_file=$model->code.'.'.$tmp->extensionName; 
			}

			if($model->save())
			{
				if(strlen(trim($model->payment_file)) > 0) {
					$tmp->saveAs(Yii::getPathOfAlias('webroot').'/image/transaction/big/'.$model->image);
				}
				$this->redirect(array('view','id'=>$model->id_transaction));
			}
		}

		$this->render('update',array(
			'model'=>$model,
			));
	}	

	public function actionVerification($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Transaction']))
		{
			$model->attributes=$_POST['Transaction'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id_transaction));
		}

		$this->render('update',array(
			'model'=>$model,
			));
	}	

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Transaction');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			));
	}

	public function actionNewIndex()
	{
		$dataProvider=new CActiveDataProvider('Transaction',array('criteria'=>array('condition'=>array('status=0'))));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			));
	}

	public function actionConfirmIndex()
	{
		$dataProvider=new CActiveDataProvider('Transaction',array('criteria'=>array('condition'=>array('status=0'))));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			));
	}
	
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Transaction('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Transaction']))
			$model->attributes=$_GET['Transaction'];

		$this->render('admin',array(
			'model'=>$model,
			));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Transaction the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Transaction::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Transaction $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='transaction-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
