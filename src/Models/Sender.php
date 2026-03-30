<?php

namespace Ajtarragona\MailRelay\Models;

class Sender extends RestModel
{
  protected $model_name = "senders";

  // Atributos retornados por el servicio
  protected $attributes = ["id", "name", "from_name", "email", "default", "confirmed", "created_at", "updated_at"];

  //atributos rellenables en el update o create
  protected $fillable = ["name", "from_name", "email", "new_sender_id"];



  public static function getDefaultSender()
  {
    $ret = self::search(["default_true" => true]);
    if ($ret && $ret->isNotEmpty()) return $ret->first();
    return null;
  }



  /**
   * Envia el mail de confirmación al remitente
   */
  public function sendConfirmationMail()
  {
    $ret = $this->call('POST', $this->model_name . '/' . $this->id . '/send_confirmation_email');
    return is_null($ret) ? false : true;
  }



  /**Necesito coger el remitente por defecto */
  public function delete(array $values = [])
  {

    //si nome pasan el id del remitente alternativo, cojo el por defecto
    if (!isset($values["new_sender_id"])) {
      $default_sender =  self::getDefaultSender();
      if ($default_sender) {
        $values["new_sender_id"] = $default_sender->id;
      }
    }


    if (isset($values["new_sender_id"])) {
      return self::destroy($this->{$this->pk}, $values);
    } else {
      return false;
    }
  }
}
