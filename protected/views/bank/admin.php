<?php
/* @var $this BankController */
/* @var $model Bank */

$this->breadcrumbs=array(
	'Banks'=>array('index'),
	'Manage',
	);

$this->pageTitle='Manage Bank';
?>

<section class="col-xs-12">

	<?php echo CHtml::link('Add Bank',
		array('create'),
		array('class' => 'btn btn-success'));
		?>
		<?php echo CHtml::link('List Bank',
			array('index'),
			array('class' => 'btn btn-success'));
			?>

			<HR>

				<?php $this->widget('zii.widgets.grid.CGridView', array(
					'id'=>'bank-grid',
					'dataProvider'=>$model->search(),
					'filter'=>$model,
					'itemsCssClass' => 'table-responsive table table-striped table-hover table-vcenter',
					'columns'=>array(

						array(
							'header'=>'No',
							'value'=>'$this->grid->dataProvider->pagination->currentPage*$this->grid->dataProvider->pagination->pageSize + $row+1',
							'htmlOptions'=>array('width'=>'10px', 
								'style' => 'text-align: center;')),

						'name',
						'code',
						'branch',
						// 'status',
						
						array(
							'header'=>'Action',
							'class'=>'CButtonColumn',
							'htmlOptions'=>array('width'=>'100px', 
								'style' => 'text-align: center;'),
							),
						),
						)); ?>

					</section>