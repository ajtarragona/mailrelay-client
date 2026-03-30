<?php

namespace Ajtarragona\MailRelay\Models;

use Ajtarragona\MailRelay\Traits\IsRestClient;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;


class RestModel implements Arrayable, JsonSerializable
{
    use IsRestClient;

    protected $model_name;
    protected $pk = "id";

    // Atributos retornados por el servicio
    protected $attributes = ["id", "created_at", "updated_at"];

    //atributos rellenables en el update o create
    protected $fillable = [];

    protected $dates = ["created_at", "updated_at"];


    // Almacén real de los datos
    protected $attributes_data = [];

    // Métodos mágicos para que $model->nombre funcione
    public function __get($key)
    {
        return $this->attributes_data[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes_data[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->attributes_data[$key]);
    }

    /**
     * Implementación de Arrayable
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return ($value instanceof Arrayable) ? $value->toArray() : $value;
        }, $this->attributes_data);
    }

    /**
     * Implementación de JsonSerializable (para json_encode($model))
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
    /**
     * Esto es lo que hace que dump($model) sea legible y útil
     */
    public function __debugInfo()
    {
        return $this->attributes_data;
    }

    protected static function castAll($array)
    {
        $ret = collect();
        if ($array && is_array($array)) {
            foreach ($array as $item) {
                $ret->push(self::cast($item));
            }
        }
        return $ret;
    }


    protected static function cast($object)
    {
        if (!$object || !is_object($object)) return null;

        $model = new static;
        foreach ($model->attributes as $attribute) {
            $value = $object->{$attribute} ?? null;

            if ($value && in_array($attribute, $model->dates)) {
                $value = \Carbon\Carbon::parse($value);
            }

            // Guardamos en el array interno, no en la propiedad dinámica
            $model->{$attribute} = $value;
        }
        return $model;
    }



    private function prepareArguments(?array $values = null)
    {
        if (!$values) return [];

        // Laravel Http Client ya convierte el array a JSON automáticamente
        return Arr::only($values, $this->fillable);
    }



    public static function all($page = null, $per_page = null)
    {
        return self::search([], $page, $per_page);
    }



    /**
     * Busca mediante parámetros 
     */
    public static function search($parameters = [], $page = null, $per_page = null)
    {
        $model = new static;

        // Construimos un array plano de parámetros
        $queryParams = array_merge($parameters, [
            'page' => $page,
            'per_page' => $per_page
        ]);

        // Eliminamos nulos para que no ensucien la URL (?page=)
        $queryParams = array_filter($queryParams, fn($value) => !is_null($value));
        // dd($queryParams);
        $ret = $model->call('GET', $model->model_name, $queryParams);
        return self::castAll($ret);
    }


    public static function find($id)
    {
        $model = new static;
        $ret = $model->call('GET', $model->model_name . '/' . $id);
        return self::cast($ret);
    }





    public static function create(array $values = [])
    {
        $model = new static;

        $args = $model->prepareArguments($values);
        $ret = $model->call('POST', $model->model_name, $args);

        return self::cast($ret);
    }





    public function update(array $values = [])
    {
        return self::updateStatic($this->{$this->pk}, $values);
    }


    public static function updateStatic($id, array $values)
    {
        $model = new static;
        $args = $model->prepareArguments($values);
        $ret = $model->call('PATCH', $model->model_name . '/' . $id, $args);

        return self::cast($ret);
    }




    public function delete(array $values = [])
    {
        return self::destroy($this->{$this->pk}, $values);
    }


    public static function destroy($id, array $values = [])
    {
        $model = new static;
        $args = $model->prepareArguments($values);
        // dump($args);
        $ret = $model->call('DELETE', $model->model_name . '/' . $id, $args);

        //nul serà si no troba l'ID
        return is_null($ret) ? false : true;
    }
}
