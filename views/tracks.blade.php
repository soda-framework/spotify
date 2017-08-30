<?php
    $title = @$title;
    $title = isset($title) ? $title : '';
?>

<tracks :playlist="{{ $playlist->id }}" :title="{{ "'" . $title . "'" }}"></tracks>
