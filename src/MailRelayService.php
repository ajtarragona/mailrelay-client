<?php

namespace Ajtarragona\MailRelay;

use Ajtarragona\MailRelay\Models\Campaign;
use Ajtarragona\MailRelay\Models\CampaignFolder;
use Ajtarragona\MailRelay\Models\CustomField;
use Ajtarragona\MailRelay\Models\Group;
use Ajtarragona\MailRelay\Models\Import;
use Ajtarragona\MailRelay\Models\MediaFile;
use Ajtarragona\MailRelay\Models\MediaFolder;
use Ajtarragona\MailRelay\Models\Sender;
use Ajtarragona\MailRelay\Models\SentCampaign;
use Ajtarragona\MailRelay\Models\SmtpEmails;
use Ajtarragona\MailRelay\Traits\IsRestClient;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MailRelayService
{

    use IsRestClient;




    /**
     * Retorna todos los remitentes
     */
    public function getSenders($page = null, $per_page = null)
    {
        return Sender::all($page, $per_page);
    }


    /**
     * Retorna un remitente
     */
    public function getSender($id)
    {
        return Sender::find($id);
    }
    /**
     * Retorna el remitente por defecto
     */
    public function getDefaultSender()
    {
        return Sender::getDefaultSender();
    }



    /**
     * Añade un remitente
     */
    public function createSender($name, $email)
    {
        return Sender::create([
            "name" => $name,
            "from_name" => $name,
            "email" => $email
        ]);
    }





    /**
     * Retorna todos los custom_fields de Mailrelay
     */
    public function getCustomFields($page = null, $per_page = null)
    {
        return CustomField::all($page, $per_page);
    }


    /**
     * Retorna un custom_fields
     */
    public function getCustomField($id)
    {
        return CustomField::find($id);
    }


    /**
     * Añade un custom field a mailrelay
     * $name: nombre corto interno
     * $label: nombre visible
     * $type : text, textarea, number, select, select_multiple, checkbox, radio_buttons, date
     * 
     * En caso de ser select, select_multiple, checkbox o radio_buttons
     * $options es un array con los nombres de las opciones
     */
    public function createCustomField($name, $label, $type = "text", $required = false, $default_value = "", $options = [])
    {

        $preparedOptions = [];
        if ($options) {
            foreach ($options as $option) {
                $preparedOptions[] = ["label" => $option];
            }
        }


        return CustomField::create([
            "label" => $label,
            "tag_name" => Str::snake($name),
            "field_type" => $type, //(text, textarea, number, select, select_multiple, checkbox, radio_buttons, date
            "required" => $required,
            "default_value" => $default_value,
            "custom_field_options_attributes" => $preparedOptions
        ]);
    }







    /**
     * Retorna todos los grupos
     */
    public function getGroups($page = null, $per_page = null)
    {
        return Group::all($page, $per_page);
    }


    /**
     * Retorna un grupo
     */
    public function getGroup($id)
    {
        return Group::find($id);
    }



    /**
     * Añade un grupo
     */
    public function createGroup($name, $description = null)
    {
        return Group::create([
            "name" => $name,
            "description" => $description
        ]);
    }



    /**
     * Retorna todoslos boletines
     */
    public function getCampaigns($page = null, $per_page = null)
    {
        return Campaign::all($page, $per_page);
    }


    /**
     * Retorna un boletin
     */
    public function getCampaign($id)
    {
        return Campaign::find($id);
    }



    /**
     * Añade un boletin
     */
    public function createCampaign($subject, $body, $sender_id, $group_ids = [], $target = "groups", $attributes = [])
    {
        $attrs = array_merge([
            "subject" => $subject,
            "html" => $body,
            "sender_id" => $sender_id,
            "group_ids" => $group_ids,
            "target" => $target,
        ], $attributes);
        // dd($attrs);

        return Campaign::create($attrs);
    }




    /**
     * Retorna todos los informes de envio de boletines
     */
    public function getSentCampaigns($page = null, $per_page = null)
    {
        return SentCampaign::all($page, $per_page);
    }


    /**
     * Retorna un informes de envio de boletin
     */
    public function getSentCampaign($id)
    {
        return SentCampaign::find($id);
    }

    /**
     * Retorna todas las carpetas de boletin
     */
    public function getCampaignFolders($page = null, $per_page = null)
    {
        return CampaignFolder::all($page, $per_page);
    }


    /**
     * Retorna una carpeta de boletin
     */
    public function getCampaignFolder($id)
    {
        return CampaignFolder::find($id);
    }



    /**
     * Añade una carpeta de boletin
     * Si ya existe con el mismo nombre, la devuelve
     */
    public function createCampaignFolder($name)
    {
        return CampaignFolder::create([
            "name" => $name
        ]);
    }


    /**
     * Retorna todas las importaciones
     */
    public function getImports($page = null, $per_page = null)
    {
        return Import::all($page, $per_page);
    }


    /**
     * Retorna una importacion
     */
    public function getImport($id)
    {
        return Import::find($id);
    }



    /**
     * Añade una importacion
     */
    public function createImport($filename, $subscribers, $group_ids = [], $callback = null, $ignore = true)
    {
        return Import::doCreate($filename, $subscribers, $group_ids, $callback, $ignore);
    }





    /**
     * Retorna todas las imagenes
     */
    public function getMediaFiles($page = null, $per_page = null)
    {
        return MediaFile::all($page, $per_page);
    }


    /**
     * Retorna una imagen
     */
    public function getMediaFile($id)
    {
        return MediaFile::find($id);
    }



    /**
     * Añade una imagen
     */
    public function createMediaFile($filename, $content, $media_folder_id = false)
    {
        return MediaFile::createFromContent($filename, $content, $media_folder_id);
    }


    /**
     * Añade una imagen de test
     */
    public function createTestMediaFile()
    {
        $content = Storage::get('bg-censat.jpg');
        // dd($content);
        return $this->createMediaFile("bg-censat.jpg", $content, 1);
    }

    /**
     * Añade una imagen a partir de un upload
     */
    public function uploadMediaFile($filename, $uploaded_file, $media_folder_id = 0)
    {
        return MediaFile::createFromUpload($filename, $uploaded_file, $media_folder_id);
    }



    /**
     * Retorna las carpetas de media
     */
    public function getMediaFolders()
    {
        return MediaFolder::all();
    }

    /**
     * Retorna una carpeta de media
     */
    public function getMediaFolder($id)
    {
        return MediaFolder::find($id);
    }



    /**
     * Añade una carpeta de media
     * Si ya existe con el mismo nombre, la devuelve
     */
    public function createMediaFolder($name)
    {
        return MediaFolder::create([
            "name" => $name
        ]);
    }


    /**
     * Envío de email transaccional (vía API v3)
     */
    public function sendRawMessage($payload)
    {
        // Asumiendo que tu Trait IsRestClient tiene un método post
        return $this->restPost('messages', $payload);
    }

    /**
     * Auxiliar para buscar remitente por email
     */
    public function getSenderByEmail($email)
    {
        // Lógica para filtrar tus remitentes
        return collect($this->getSenders())->firstWhere('email', $email);
    }

    public function getSmtpEmails()
    {
        return SmtpEmails::all();
    }


    public function getSmtpEmailsByTag(string $tag, ?bool $includeMeta = false, ?array $parameters = [])
    {
        $args = ['q[smtp_tag_eq]' => $tag];
        if ($includeMeta) {
            $args['include_impressions'] = true;
            $args['include_clicks'] = true;
            $args['include_smtp_tags'] = true;
        }
        $args = array_merge($args, $parameters);
        return SmtpEmails::search($args);
    }

    public function getSmtpEmail($id)
    {
        return SmtpEmails::find($id);
    }

    public function getSmtpEmailByMessageId(string $messageId, ?bool $includeMeta = false, ?array $parameters = [])
    {
        $messageId = '<' . trim($messageId, "<>") . '>'; // Mailrelay a veces devuelve el message_id con <> y a veces sin

        $args = ['q[message_id_eq]' => $messageId];
        if ($includeMeta) {
            $args['include_impressions'] = true;
            $args['include_clicks'] = true;
            $args['include_smtp_tags'] = true;
        }
        $args = array_merge($args, $parameters);
        return SmtpEmails::search($args)->first();
    }

    public function getSmtpEmailsTo(string $email, ?bool $includeMeta = false, ?array $parameters = [])
    {
        $args = ['q[email_eq]' => $email];
        if ($includeMeta) {
            $args['include_impressions'] = true;
            $args['include_clicks'] = true;
            $args['include_smtp_tags'] = true;
        }
        $args = array_merge($args, $parameters);
        return SmtpEmails::search($args);
    }
}
