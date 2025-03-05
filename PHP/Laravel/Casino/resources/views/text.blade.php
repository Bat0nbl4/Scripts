<form method="post" action="<?php base_url(); ?>report/create_sum_report" class="flex gap-20">
    <input type="hidden" name="user_<?php echo user_id().'__'.user_division();?>" value="on" />
    <div>
        <label for="name">Введите название отчета (понятное для Вас)</label>
        <input type="text" id="name" placeholder="наименование формы" name="name" value="">
    </div>
    <div>
        <span>С</span>
        <select name="from" id="from">
            <?php foreach($periods as $val):?>
            <option val="<?php echo date('m.Y',strtotime($val['period'])); ?>" ><?php echo date('m.Y',strtotime($val['period']));?></option>
            <?php endforeach;?>
        </select>
        <span> по </span>
        <select name="from" id="from">
            <?php foreach($periods as $val):?>
            <option val="<?php echo date('m.Y',strtotime($val['period'])); ?>" ><?php echo date('m.Y',strtotime($val['period']));?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div>
        <select name="form_select">
            <?php foreach($forms_summary as $key=>$val):?>
            <option value="<?php echo $val['form_id'];?>"<?php echo ($val['selected']?' selected="selected"':'');?>><?php echo $val['form_name'];?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div>
        <button type="submit" class="btn">Добавить</button>
    </div>
</form>
<?php echo form_open(base_url().'report/create_sum_report',array('class'=>'select_type_user'));?>

    <div class="w_row">
        <div class="label">Введите название отчета (понятное для Вас)</div>
        <input type="text" placeholder="наименование формы" name="name" value="<?php echo $simple_name;?>">
    </div>
    <div class="w_row inline">
        <div class="label full">Введите период за который отчеты будут суммироваться</div>
        <div class="label">c</div>
        <select name="from">
            <?php foreach($periods as $val):?>
            <option val="<?php echo date('m.Y',strtotime($val['period']));?>" <?php echo check_sp_var(@$current_period,'from',date('m.Y',strtotime($val['period'])));?>><?php echo date('m.Y',strtotime($val['period']));?></option>
            <?php endforeach;?>
        </select>
        <div class="label">по</div>
        <select name="to">
            <?php foreach($periods as $val):?>
            <option val="<?php echo date('m.Y',strtotime($val['period']));?>" <?php echo check_sp_var(@$current_period,'to',date('m.Y',strtotime($val['period'])));?>><?php echo date('m.Y',strtotime($val['period']));?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div class="w_row">
        <div class="label">Выберите форму</div>
        <select name="form_select">
            <?php foreach($forms_summary as $key=>$val):?>
            <option value="<?php echo $val['form_id'];?>"<?php echo ($val['selected']?' selected="selected"':'');?>><?php echo $val['form_name'];?></option>
            <?php endforeach;?>
        </select>
    </div>

    <div class="w_row submit">
        <input type="submit" class="like-button blue" value="<?php echo $select_type_form['btn_ok'];?>"/>
        <div class="like-button orange cancel">Отменить</div>
    </div>
<?php echo form_close();?>
