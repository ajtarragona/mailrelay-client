<?php

namespace Ajtarragona\MailRelay\Models;

class CampaignFolder extends RestModel
{
  protected $model_name = "campaign_folders";

  // Atributos retornados por el servicio
  protected $attributes = ["id", "name", "created_at", "updated_at"];

  //atributos rellenables en el update o create
  protected $fillable = ["name"];

  protected $dates = ["created_at", "updated_at"];



  public static function create(array $values = [])
  {
    if (!isset($values["name"])) return null;

    //si ya existe con el mismo nombre la devuelvo
    $current = self::search(["q[name_eq]" => $values["name"]]);
    if ($current->isNotEmpty()) return $current->first();


    //si no, la creo llamando al create el RestModel
    return parent::create($values);
  }
}
