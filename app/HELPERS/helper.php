<?php

function imageUrl($image)
{
    return env('ADMIN_PANEL_URL') . env('PRODUCT_IMAGES_PATH') . $image;
}



function salePercent($price,$salePrice)
{
    return round((($price - $salePrice) / $price) * 100);
}
