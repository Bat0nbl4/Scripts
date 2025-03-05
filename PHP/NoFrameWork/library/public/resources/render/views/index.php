<?php \vendor\rendering\View::template(); ?>
<?php \vendor\rendering\View::title("index"); ?>

<form action="#">
    <label for="search" class="search">
        <button class="btn blue" type="submit">Поиск</button>
        <input type="text" id="search">
        <img class="loupe" src="<?php echo \vendor\helpers\Resource::resource("img/system/loupe.svg") ?>">
    </label>
</form>

<script src="<?php echo \vendor\helpers\Resource::resource("js/slider.js") ?>" type="text/javascript"></script>
<div class="slider">
    <div class="slider-wrapper">
        <img src="<?php echo \vendor\helpers\Resource::resource("img/book/1.jpg") ?>" alt="">
        <img src="<?php echo \vendor\helpers\Resource::resource("img/book/2.jpg") ?>" alt="">
        <img src="<?php echo \vendor\helpers\Resource::resource("img/book/3.jpg") ?>" alt="">
    </div>
</div>