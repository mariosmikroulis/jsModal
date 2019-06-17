<?php


class utilities
{
    public static function isFloat($num)
    {
        return is_float($num) || is_numeric($num) && ((float) $num != (int) $num);
    }

    public static function replaceText($content, $data)
    {
        if(count((array)$data) > 0)
        {
            foreach((array)$data as $key => $val)
            {
                $content = str_replace("{{".$key."}}",$val, $content);
            }
        }

        return $content;
    }
}