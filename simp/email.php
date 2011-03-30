<?
namespace simp;

class Email
{
    /// given the name of a message template, sends an email based on data provided.
    /// @param $message_template name of email message template to use
    /// @param $email_data associative array consisting of the following fields:
    ///     to: array of email addresses of recipients
    ///     from: address of sender
    ///     subject: Subject of email
    ///     type: 'html' or 'plain'
    ///     data: a variable containing data to use in template
    ///
    /// If the type is 'html', the message template name will be appended with ".phtml"
    /// to load the html version of the email.
    /// Otherwise, if it is 'plain', the message template name will be appended with 
    /// "_txt.phtml"
    static public function Send($message_template, $email_data)
    {
        global $APP_BASE_PATH;
        $data = array();
        if (array_key_exists('data', $email_data)) $data = $email_data['data'];
        $ext = $email_data['type'] == "html" ? ".phtml" : "_txt.phtml";
        ob_start();
        require_once $APP_BASE_PATH . "/emails/" . SnakeCase($message_template) . $ext;
        $message = ob_get_contents();
        ob_end_clean();
        $headers = $email_data['type'] == "html" ?
            "MIME-Version: 1.0" . "\r\n" .
            "Content-type: text/html; charset=iso-8859-1" . "\r\n"
            :
            "";
        $headers .= 
            "From: " . $email_data['from'] . "\r\n" .
            "Reply-To: " . $email_data['from'] . "\r\n" .
            "X-Mailer: PHP/:" . phpversion();
        return mail($email_data['to'], $email_data['subject'], $message, $headers);
    }

}
