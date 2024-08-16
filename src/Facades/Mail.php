<?php

namespace PulseFrame\Facades;

use PulseFrame\Facades\Env;
use PulseFrame\facades\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Class Mail
 * 
 * @category facades
 * @name Mail
 * 
 * This class handles sending emails using the PHPMailer library. It loads SMTP settings from environment 
 * variables, allows for sending emails with HTML or plain text content, and supports attachments.
 */
class Mail
{
  protected static $instance;
  protected $mailer;

  /**
   * Mail constructor.
   *
   * @category facades
   * 
   * This private constructor initializes the PHPMailer instance with SMTP settings loaded from the 
   * environment variables using the Env facade. It sets the SMTP host, authentication credentials, 
   * encryption type, port, and the default sender's email and name.
   * 
   * Example usage:
   * $mail = new Mail(); // This will not work because the constructor is private.
   */
  private function __construct()
  {
    $this->mailer = new PHPMailer(true);
    $this->mailer->isSMTP();
    $this->mailer->Host = Env::get('smtp.host');
    $this->mailer->SMTPAuth = true;
    $this->mailer->Username = Env::get('smtp.username');
    $this->mailer->Password = Env::get('smtp.password');
    $this->mailer->SMTPSecure = Env::get('smtp.encryption');
    $this->mailer->Port = Env::get('smtp.port');
    $this->mailer->setFrom(Env::get('smtp.from_address'), Env::get('smtp.from_name'));
  }

  /**
   * Get the Mail instance.
   *
   * @category facades
   * 
   * @return Mail The singleton instance of the Mail class.
   *
   * This static function returns the singleton instance of the Mail class. If the instance does not exist yet, 
   * it creates a new one.
   * 
   * Example usage:
   * $mail = Mail::getInstance();
   */
  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Send an email.
   *
   * @category facades
   * 
   * @param string $to The recipient's email address.
   * @param string $subject The email subject.
   * @param string $body The HTML content of the email.
   * @param string $altBody The plain text alternative content of the email (optional).
   * @param array $attachments An array of file paths or associative arrays of file paths and names to attach to the email (optional).
   * @return bool True on success, False on failure.
   *
   * This static function sends an email using the PHPMailer instance. It sets the recipient, subject, HTML and 
   * plain text bodies, and attaches any provided files. It returns a boolean indicating the success or failure 
   * of the email sending process.
   * 
   * Example usage:
   * $result = Mail::send('recipient@example.com', 'Subject', '<b>HTML content</b>', 'Plain text content', ['/path/to/file1', '/path/to/file2']);
   */
  public static function send($to, string $subject, $body, $altBody = '', $attachments = [])
  {
    $instance = self::getInstance();

    try {
      $instance->mailer->clearAddresses();
      $instance->mailer->clearAttachments();

      $instance->mailer->addAddress($to);

      $instance->mailer->isHTML(true);
      $instance->mailer->Subject = Config::get('app', 'stage') === 'development' ? "Development - " . $subject : $subject;
      $instance->mailer->Body = $body;

      if ($altBody !== '' && $altBody !== false) {
        $instance->mailer->AltBody = $altBody;
      }

      if (isset($attachments) && is_array($attachments)) {
        foreach ($attachments as $fn => $a) {
          if (is_numeric($fn)) {
            $instance->mailer->addAttachment($a);
          } else {
            $instance->mailer->addAttachment($a, $fn);
          }
        }
      }

      return $instance->mailer->send();
    } catch (Exception $e) {
      throw new \Exception($e);
    }
  }
}
