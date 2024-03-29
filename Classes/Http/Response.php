<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Http;


use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\StrictException;
use PHPHtmlParser\Options;

class Response
{

    protected $statusCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'Unsupported Version'
    ];

    public int $status;

    public $headers;

    private $content;

    private $dom;


    public function __construct($content = '', $status = 200, $headers = [])
    {

        $this->headers = new ParamStore($headers);
        $this->dom = new Dom();
        $this->dom->setOptions(
            (new Options())
                ->setRemoveScripts(false)
                ->setRemoveStyles(false)
        );

        $this->setContent($content);
        $this->setStatusCode($status);
    }

    public function setStatusCode(int $code): void
    {
        $this->status = $code;
    }

    public function getStatusCode() : int
    {
        return $this->status;
    }

    public function getStatusText()
    {
        return $this->statusCodes[$this->getStatusCode()];
    }

    public function setContent($content)
    {
        $this->content = (string)$content;
        try {
            $this->dom->loadStr((string)$content);
        } catch (ChildNotFoundException $e) {
        } catch (CircularException $e) {
        } catch (ContentLengthException $e) {
        } catch (LogicalException $e) {
        } catch (StrictException $e) {
        }
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getBody() {
        /** @var Dom\Node\InnerNode $body */
        $body = $this->dom->find('body',0);
        return $body->innerhtml;
    }

    public function sendHeaders()
    {

        if (headers_sent()) {
            return;
        }

        header(sprintf('HTTP/1.1 %s %s', $this->status, $this->getStatusText()), true, $this->status);

        foreach ($this->headers->all() as $name => $value) {

            /*
                Multiple message-header fields with the same field-name MAY be present in a message
                if and only if the entire field-value for that header field is defined as a comma-separated list
                http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
            */

            $values = is_array($value) ? $value : [$value];

            // false = do not replace previous identical header
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }
    }

    public function send()
    {
        $this->sendHeaders();
        echo $this->content;
    }

    /**
     * @return Dom
     */
    public function getDom(): Dom
    {
        return $this->dom;
    }

}
