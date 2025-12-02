<?php
/**
 * Simple SMTP Email Sender using PHPMailer-like approach
 * This is a lightweight solution that works without external dependencies
 */

class SimpleSMTP {
    private $host;
    private $port;
    private $username;
    private $password;
    private $secure; // 'tls' or 'ssl'
    private $connection;
    
    public function __construct($host, $port = 587, $username = '', $password = '', $secure = 'tls') {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->secure = $secure;
    }
    
    public function send($fromEmail, $fromName, $toEmail, $subject, $htmlBody) {
        try {
            // Connect to SMTP server
            $this->connect();
            
            // Authenticate
            if (!$this->authenticate()) {
                return false;
            }
            
            // Send email
            $result = $this->sendEmail($fromEmail, $fromName, $toEmail, $subject, $htmlBody);
            
            // Close connection
            $this->disconnect();
            
            return $result;
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function connect() {
        $host = ($this->secure === 'ssl') ? 'ssl://' . $this->host : $this->host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $this->connection = @stream_socket_client(
            $host . ':' . $this->port,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$this->connection) {
            throw new Exception("Failed to connect: $errstr ($errno)");
        }
        
        $this->readResponse();
    }
    
    private function authenticate() {
        $this->sendCommand("EHLO " . $this->host);
        
        if ($this->secure === 'tls') {
            $this->sendCommand("STARTTLS");
            if (substr($this->readResponse(), 0, 3) != '220') {
                return false;
            }
            stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand("EHLO " . $this->host);
        }
        
        $this->sendCommand("AUTH LOGIN");
        $this->readResponse();
        
        $this->sendCommand(base64_encode($this->username));
        $this->readResponse();
        
        $this->sendCommand(base64_encode($this->password));
        $response = $this->readResponse();
        
        return substr($response, 0, 3) == '235';
    }
    
    private function sendEmail($fromEmail, $fromName, $toEmail, $subject, $htmlBody) {
        $this->sendCommand("MAIL FROM: <$fromEmail>");
        $this->readResponse();
        
        $this->sendCommand("RCPT TO: <$toEmail>");
        $this->readResponse();
        
        $this->sendCommand("DATA");
        $this->readResponse();
        
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "To: <$toEmail>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        $this->sendCommand($headers . "\r\n" . $htmlBody . "\r\n.");
        $response = $this->readResponse();
        
        return substr($response, 0, 3) == '250';
    }
    
    private function sendCommand($command) {
        fputs($this->connection, $command . "\r\n");
    }
    
    private function readResponse() {
        $response = '';
        while ($line = fgets($this->connection, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $response;
    }
    
    private function disconnect() {
        if ($this->connection) {
            $this->sendCommand("QUIT");
            fclose($this->connection);
        }
    }
}

?>

