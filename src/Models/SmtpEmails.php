<?php

namespace Ajtarragona\MailRelay\Models;

class SmtpEmails extends RestModel
{
  protected $model_name = "smtp_emails";

  // Atributos retornados por el servicio
  protected $attributes = ["id", "name", "created_at", "updated_at"];

  //atributos rellenables en el update o create
  protected $fillable = [];

  protected $dates = ["processed_at", "delivered_at", "soft_bounced_at"];



  public static function create(array $values = [])
  {
    if (!isset($values["name"])) return null;

    //si ya existe con el mismo nombre la devuelvo
    $current = self::search(["name_eq" => $values["name"]]);
    if ($current->isNotEmpty()) return $current->first();


    //si no, la creo llamando al create el RestModel
    return parent::create($values);
  }
}
