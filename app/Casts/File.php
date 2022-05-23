<?php

namespace App\Casts;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Storage;

class File implements CastsAttributes
{
    protected $prefix;

    public function __construct()
    {
        $this->prefix = env('APP_URL')."/file";
        // $this->prefix = "/file";
    }

    /**
     * Преобразовать значение к пользовательскому типу.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  string  $value
     * @param  array  $attributes
     * @return \App\Models\Address
     */
    public function get($model, $key, $value, $attributes)
    {
        return strlen($value)>5?"$this->prefix/$value":'';
    }

    /**
     * Подготовить переданное значение к сохранению.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  string  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        return str_ireplace($this->prefix, '', $value);
    }
}
