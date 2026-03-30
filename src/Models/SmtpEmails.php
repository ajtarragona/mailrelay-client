<?php

namespace Ajtarragona\MailRelay\Models;

class SmtpEmails extends RestModel
{
  protected $model_name = "smtp_emails";

  protected $attributes = ["id", "subscriber_id", "email", "status", "processed_at", "delivered_at", "bounced_at", "bounce_category", "bounce_message", "soft_bounced_at", "updated_at", "reported_as_spam", "message_id", "sender_email", "used_credits", "impressions", "smtp_tags"];


  protected $dates = ["processed_at", "delivered_at", "bounced_at", "soft_bounced_at", "updated_at"];

  /** No tiene create */
  public static function create(array $values = [])
  {
    return false;
  }

  /* NO tiene update */
  public static function updateStatic($id, array $values)
  {
    return false;
  }

  /* NO tiene delete */
  public static function destroy($id, array $values = [])
  {
    return false;
  }
}
