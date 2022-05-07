<?php
/*
siteFactoryServer <https://github.com/siteFactorySource/siteFactoryServer>
Copyright (C) 2022 Lukas Tautz

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

You can use siteFactoryServer for free in your projects, you can also modify the siteFactoryCSS files BUT YOU ARE NOT ALLOWED TO DELETE THIS COMMENT!
*/
class prettyPrint
{
	private string $input = '';
	private string $output = '';
	private int $tabs = 0;
	private bool $in_tag = false;
	private bool $in_comment = false;
	private bool $in_content = false;
	private bool $inline_tag = false;
	private int $input_index = 0;
    private function parseComment()
	{
		if ($this -> isEndComment())
        {
			$this -> in_comment = false;
			$this -> output .= '- -> ';
			$this -> input_index += 3;
		}
        else
        {
			$this -> output .= $this -> input[$this -> input_index];
		}
	}
	private function parseInnerTag()
	{
		if ($this -> input[$this -> input_index] == '>')
        {
			$this -> in_tag = false;
			$this -> output .= '>';
        }
        else
        {
			$this -> output .= $this -> input[$this -> input_index];
		}
	}
	private function parseInnerInlineTag()
	{
		if ($this -> input[$this -> input_index] == '>')
        {
			$this -> inline_tag = false;
			$this -> decrementTabs();
			$this -> output .= '>';
		}
        else
        {
			$this -> output .= $this -> input[$this -> input_index];
		}
	}
	private function parseTag()
	{
		if ($this -> isComment())
        {
			$this -> output .= "\n" . str_repeat("\t", $this -> tabs);
			$this -> in_comment = true;
		}
        elseif ($this -> isEndTag())
        {
			$this -> in_tag = true;
			$this -> inline_tag = false;
			$this -> decrementTabs();
			if(!$this -> isInlineTag() &&!$this -> isTagEmpty())
            {
				$this -> output .= "\n" . str_repeat("\t", $this -> tabs);
			}
		}
        else
        {
			$this -> in_tag = true;
			if (!$this -> in_content &&!$this -> inline_tag)
            {
				$this -> output .= "\n" . str_repeat("\t", $this -> tabs);
			}
			if (!$this -> isClosedTag())
            {
				$this -> tabs++;
			}
			if ($this -> isInlineTag())
            {
				$this -> inline_tag = true;
			}
		}
	}
	private function isEndTag()
	{
		for ($input_index = $this -> input_index; $input_index < strlen($this -> input); $input_index++)
        {
			if ($this -> input[$input_index] == '<' && $this -> input[$input_index + 1] == '/')
            {
				return true;
			}
            elseif ($this -> input[$input_index] == '<' && $this -> input[$input_index + 1] == '!')
            {
				return true;
			}
            elseif ($this -> input[$input_index] == '>')
            {
				return false;
			}
		}
		return false;
	}
	private function decrementTabs()
	{
		$this -> tabs--;
		if ($this -> tabs < 0)
        {
			$this -> tabs = 0;
		}
	}
	private function isComment()
	{
		if ($this -> input[$this -> input_index] == '<' && $this -> input[$this -> input_index + 1] == '!' && $this -> input[$this -> input_index + 2] == '-' && $this -> input[$this -> input_index + 3] == '-')
        {
			return true;
		}
        else
        {
			return false;
		}
	}
	private function isEndComment()
	{
		if ($this -> input[$this -> input_index] == '-'	&& $this -> input[$this -> input_index + 1] == '-' && $this -> input[$this -> input_index + 2] == '>')
        {
			return true;
		}
        else
        {
			return false;
		}
	}
	private function isTagEmpty()
	{
		$current_tag = $this -> getCurrentTag($this -> input_index + 2);
		$in_tag = false;
		for ($input_index = $this -> input_index - 1; $input_index >= 0; $input_index--)
        {
			if (!$in_tag)
            {
				if ($this -> input[$input_index] == '>')
                {
					$in_tag = true;
				}
                elseif (!preg_match('/\s/', $this -> input[$input_index]))
                {
					return false;
				}
			}
            else
            {
				if ($this -> input[$input_index] == '<')
                {
					if ($current_tag == $this -> getCurrentTag($input_index + 1))
                    {
						return true;
					}
                    else
                    {
						return false;
					}
				}
			}
		}
		return true;
	}
	private function getCurrentTag($input_index)
	{
		$current_tag = '';
		for ($input_index; $input_index < strlen($this -> input); $input_index++)
        {
			if ($this -> input[$input_index] == '<')
            {
				continue;
			}
            elseif ($this -> input[$input_index] == '>' || preg_match('/\s/', $this -> input[$input_index]))
            {
				return $current_tag;
			}
            else
            {
				$current_tag .= $this -> input[$input_index];
			}
		}
		return $current_tag;
	}
	private function isClosedTag()
	{
		$closed_tags = ['meta', 'link', 'img', 'hr', 'br', 'input'];
		$current_tag = '';
		
		for ($input_index = $this -> input_index; $input_index < strlen($this -> input); $input_index++)
        {
			if ($this -> input[$input_index] == '<')
            {
				continue;
			}
            elseif (preg_match('/(\>|\s|\/)/', $this -> input[$input_index]))
            {
				break;
			}
            else
            {
				$current_tag .= $this -> input[$input_index];
				
			}
		}
		if (in_array($current_tag, $closed_tags))
        {
			return true;
		}
        else
        {
			return false;
		}
	}
	private function isInlineTag()
	{
		$inline_tags = ['title', 'a', 'span', 'abbr', 'acronym', 'b', 'basefont', 'bdo', 'big', 'cite', 'code', 'dfn', 'em', 'font', 'i', 'kbd', 'q', 's', 'samp', 'small', 'strike', 'strong', 'sub', 'sup', 'textarea', 'tt', 'u', 'var', 'del', 'pre'];
		$current_tag = '';
		for ($input_index = $this -> input_index; $input_index < strlen($this -> input); $input_index++)
        {
			if ($this -> input[$input_index] == '<' || $this -> input[$input_index] == '/')
            {
				continue;
			}
            elseif (preg_match('/\s/', $this -> input[$input_index]) || $this -> input[$input_index] == '>')
            {
				break;
			}
            else
            {
				$current_tag .= $this -> input[$input_index];
			}
		}
		if (in_array(strtolower($current_tag), $inline_tags))
        {
			return true;
		}
        else
        {
			return false;
		}
	}
	public function html($input)
	{
		$this -> input = $input;
		$this -> output = '';
		$starting_index = 0;
		if (preg_match('/<\!doctype/i', $this -> input))
        {
			$starting_index = strpos($this -> input, '>') + 1;
			$this -> output .= substr($this -> input, 0, $starting_index);
		}
		for ($this -> input_index = $starting_index; $this -> input_index < strlen($this -> input); $this -> input_index++)
        {
			if ($this -> in_comment)
            {
				$this -> parseComment();
			}
            elseif ($this -> in_tag)
            {
				$this -> parseInnerTag();
			}
            elseif ($this -> inline_tag)
            {
				$this -> parseInnerInlineTag();
			}
            else
            {
				if (preg_match('/[\r\n\t]/', $this -> input[$this -> input_index]))
                {
					continue;
				}
                elseif ($this -> input[$this -> input_index] == '<')
                {
					if (!$this -> isInlineTag())
                    {
					    $this -> in_content = false;
				    }
					$this -> parseTag();
				}
                elseif (!$this -> in_content)
                {
					if (!$this -> inline_tag)
                    {
					    $this -> output .= "\n" . str_repeat("\t", $this -> tabs);
				    }
					$this -> in_content = true;
				}
				$this -> output .= $this -> input[$this -> input_index];
			}
		}
		return $this -> output;
	}
}
if (!function_exists('http_response_code'))
{
	function http_response_code($code = null)
	{
		if ($code != null)
        {
			$code = intval($code);
			switch ($code)
			{
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default: $code = 500; $text = 'Internal Server Error'; break;
			}
			if (isset($_SERVER['SERVER_PROTOCOL']))
			{
				$protocol = $_SERVER['SERVER_PROTOCOL'];
			}
			else
			{
				$protocol = 'HTTP/1.0';
			}
			header($protocol . ' ' . $code . ' ' . $text);
			$return = true;
		}
		else
		{
			if (isset($GLOBALS['http_response_code']))
			{
				$return = $GLOBALS['http_response_code'];
			}
			else
			{
				$return = 200;
			}
		}
		return $return;
	}
}
class WebServer
{
	private int $statusCode = 200;
	private string $contentType = 'text/html; charset=UTF-8';
	public function __construct(int $statusCode = 200)
	{
		ob_start();
		$this -> statusCode = $statusCode;
		header_remove('Content-Type');
        header_remove('content-type');
	}
	public function setStatusCode(int $statusCode)
	{
		$this -> statusCode = $statusCode;
	}
	public function flush()
	{
		http_response_code($this -> statusCode);
		header('Content-Type:' . $this -> contentType);
		ob_end_flush();
	}
	public function redirectTo(string $url)
	{
		ob_end_clean();
		header('Location: ' . $url);
	}
	public function setContentType(string $contentType = 'HTML')
	{
		switch (strtolower($contentType))
		{
			case 'html': $contentType = 'text/html; charset=UTF-8'; break;
			case 'javascript': $contentType = 'text/javascript; charset=UTF-8'; break;
			case 'js': $contentType = 'text/javascript; charset=UTF-8'; break;
			case 'css': $contentType = 'text/javascript; charset=UTF-8'; break;
			case 'txt': $contentType = 'text/plain; charset=UTF-8'; break;
			case 'text': $contentType = 'text/plain; charset=UTF-8'; break;
			case 'zip': $contentType = 'application/zip; charset=UTF-8'; break;
			case 'pdf': $contentType = 'application/pdf; charset=UTF-8'; break;
			case 'xml': $contentType = 'text/xml; charset=UTF-8'; break;
			case 'default': $contentType = 'text/html; charset=UTF-8'; break;
			case 'json': $contentType = 'application/json; charset=UTF-8'; break;
			case 'mp4': $contentType = 'video/mp4; charset=UTF-8'; break;
			case 'mp3': $contentType = 'audio/mpeg; charset=UTF-8'; break;
			case 'wav': $contentType = 'audio/wav; charset=UTF-8'; break;
			case 'bmp': $contentType = 'image/bmp; charset=UTF-8'; break;
			case 'gif': $contentType = 'image/gif; charset=UTF-8'; break;
			case 'ief': $contentType = 'image/ief; charset=UTF-8'; break;
			case 'jpg': $contentType = 'image/jpeg; charset=UTF-8'; break;
			case 'png': $contentType = 'image/png; charset=UTF-8'; break;
			case 'jpeg': $contentType = 'image/jpeg; charset=UTF-8'; break;
			case 'svg': $contentType = 'image/svg+xml; charset=UTF-8'; break;
			case 'tiff': $contentType = 'image/tiff;audio/wav; charset=UTF-8'; break;
			case 'ico': $contentType = 'image/x-icon; charset=UTF-8'; break;
			case 'icon': $contentType = 'image/x-icon; charset=UTF-8'; break;
			case 'ogg': $contentType = 'video/ogg; charset=UTF-8'; break;
			case 'quicktime': $contentType = 'video/quicktime; charset=UTF-8'; break;
			case 'webm': $contentType = 'video/webm; charset=UTF-8'; break;
			case 'avi': $contentType = 'video/x-msvideo; charset=UTF-8'; break;
			default: $contentType = $contentType;
		}
		$this -> contentType = $contentType;
	}
	public function getHeaders()
	{
		return headers_list();
	}
	public function header(string $name, string $value, string $splitSymbol = ': ')
	{
		return header($name . $splitSymbol . $value);
	}
	public function rawHeader(string $rawHeader)
	{
		return header($rawHeader);
	}
	public function remove_header(string $header)
	{
		header_remove($header);
	}
	public function noCache()
	{
		header_remove('Cache-Control');
        header_remove('cache-control');
		header_remove('expires');
		header_remove('Expires');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
	}
	public function setCache(int $cacheSeconds)
	{
		header_remove('Cache-Control');
        header_remove('cache-control');
		return header('Cache-Control: max-age=' . strval($cacheSeconds));
	}
}
class DOM
{
    private DOMDocument $dom;
    private bool $prettyPrint = false;
    public function __construct(string $data = '', $prettyPrint = false)
    {
        set_error_handler(function ($number, $error)
        {
            if (preg_match('/domdocument/i', $error))
            {
                echo '';
            }
            else
            {
                return false;
            }
        });
        $this -> prettyPrint = $prettyPrint;
        $this -> dom = new DOMDocument();
        if ($data != '')
        {
			$html = $this -> dom -> createElement('html');
			$html = $this -> dom -> appendChild($html);
            $html -> appendChild($this -> htmlWithHead($data));
        }
        return $this -> dom;
    }
    public function prettyPrint(?bool $newValue = null)
    {
        if ($newValue != null)
        {
            $this -> prettyPrint = $newValue;
        }
        else
        {
            $this -> prettyPrint = !$this -> prettyPrint;
        }
    }
    public function createAttribute(string $localName)
    {
        return $this -> dom -> createAttribute($localName);
    }
    public function createComment(string $data)
    {
        return $this -> dom -> createComment($data);
    }
    public function createDocumentFragment()
    {
        return $this -> dom -> createDocumentFragment();
    }
    public function createElement(string $localName, string $value = "")
    {
        if (strtolower($localName) != 'style' && strtolower($localName) != 'script')
        {
            return $this -> dom -> createElement($localName, $value);
        }
        else
        {
            return $this -> dom -> createElement($localName, '/*' . bin2hex($value) . '*/');
        }
    }
    public function createElementNS(?string $namespace, string $qualifiedName, string $value = "")
    {
        return $this -> dom -> createElementNS($namespace, $qualifiedName, $value);
    }
    public function createEntityReference(string $name)
    {
        return $this -> dom -> createEntityReference($name);
    }
    public function createProcessingInstruction(string $target, string $data = "")
    {
        return $this -> dom -> createProcessingInstruction($target, $data);
    }
    public function createTextNode(string $data)
    {
        return $this -> dom -> createTextNode($data);
    }
    public function getElementById(string $elementId)
    {
        return $this -> dom -> getElementById($elementId);
    }
    public function getElementsByTagName(string $qualifiedName)
    {
        return $this -> dom -> getElementsByTagName($qualifiedName);
    }
    public function importNode(DOMNode $node, bool $deep = false)
    {
        return $this -> dom -> importNode($node, $deep);
    }
    public function load(string $filename, int $options = 0)
    {
        return $this -> dom -> load($filename, $options);
    }
    public function loadHTML(string $source, int $options = 0)
    {
        return $this -> dom -> loadHTML($this -> replaceInvalidTags($source), $options);
    }
    public function loadHTMLFile(string $filename, int $options = 0)
    {
        return $this -> dom -> loadHTMLFile($filename, $options);
    }
    public function appendChild(DOMNode $node)
    {
        return $this -> dom -> appendChild($node);
    }
    public function getNodePath()
    {
        return $this -> dom -> getNodePath();
    }
    public function hasAttributes()
    {
        return $this -> dom -> hasAttributes();
    }
    public function hasChildNodes()
    {
        return $this -> dom -> hasChildNodes();
    }
    public function normalize()
    {
        return $this -> dom -> normalize();
    }
    public function removeChild(DOMNode $child)
    {
        return $this -> dom -> removeChild($child);
    }
    public function replaceChild(DOMNode $node, DOMNode $child)
    {
        return $this -> dom -> removeChild($node, $child);
    }
    public function md5()
    {
        $data = $this -> render('<!DOCTYPE MD5>', false, false);
        return md5($data);
    }
    public function render(string $doctype = '<!DOCTYPE html>', bool $outputResult = true, ?bool $prettyPrint = null)
    {
        if ($prettyPrint != null)
        {
            $prettyPrint = $prettyPrint;
        }
        else
        {
            $prettyPrint = $this -> prettyPrint;
        }
        $dom = $this -> dom;
        $html = $dom -> saveHTML();
        $html = preg_replace('/^\<\!DOCTYPE(.*?)\>/i', '', $html);
        $html = str_replace("\r", '', $html);
        $html = str_replace("\n", '', $html);
        $html = $doctype . $html;
        if ($outputResult == true)
        {
            if ($prettyPrint == true)
            {
                $prettyPrintObject = new prettyPrint();
                $html = $prettyPrintObject -> html($html);
                $html = preg_replace_callback('/\<style(.*?)\>[\S\s]*?\<\/style\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/style\>/', '>$1</style>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                $html = preg_replace_callback('/\<script(.*?)\>[\S\s]*?\<\/script\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/script\>/', '>$1</script>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                echo $html;
            }
            else
            {
                $html = preg_replace_callback('/\<style(.*?)\>[\S\s]*?\<\/style\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/style\>/', '>$1</style>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                $html = preg_replace_callback('/\<script(.*?)\>[\S\s]*?\<\/script\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/script\>/', '>$1</script>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                echo $html;
            }
        }
        else
        {
            if ($prettyPrint == true)
            {
                $prettyPrintObject = new prettyPrint();
                $html = $prettyPrintObject -> html($html);
                $html = preg_replace_callback('/\<style(.*?)\>[\S\s]*?\<\/style\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/style\>/', '>$1</style>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                $html = preg_replace_callback('/\<script(.*?)\>[\S\s]*?\<\/script\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/script\>/', '>$1</script>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                return $html;
            }
            else
            {
                $html = preg_replace_callback('/\<style(.*?)\>[\S\s]*?\<\/style\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/style\>/', '>$1</style>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                $html = preg_replace_callback('/\<script(.*?)\>[\S\s]*?\<\/script\>/i', function ($array)
                {
                    $outerHTML = $array[0];
                    $outerHTML = preg_replace('/\>[\S\s]*?\/\*(.*?)\*\/[\S\s]*?\<\/script\>/', '>$1</script>', $outerHTML);
                    $hexData = preg_replace('/^(.*?)\>(.*?)\<(.*?)$/', '$2', $outerHTML);
                    $outerHTML = str_replace($hexData, hex2bin($hexData), $outerHTML);
                    return $outerHTML;
                }, $html);
                return $html;
            }
        }
    }
	public function html(string $html)
    {
        $html = $this -> replaceInvalidTags($html);
		$htmlElement = $this -> dom -> createDocumentFragment();
		$tempDom = new DOMDocument();
		$html = htmlentities($html, ENT_NOQUOTES, 'UTF-8', false);
		$html = str_replace(['&lt;', '&gt;', '&amp;lt;', '&amp;gt;'], ['<', '>', '&lt;', '&gt;'], $html);
		$tempDom -> loadHTML($html);
		foreach ($tempDom -> getElementsByTagName('body')[0] -> childNodes as $node)
		{
			$node = $this -> dom -> importNode($node, true);
			$htmlElement -> appendChild($node);
		}
		return $htmlElement;
	}
	public function htmlWithHead(string $html)
    {
        $html = $this -> replaceInvalidTags($html);
		$htmlElement = $this -> dom -> createDocumentFragment();
		$tempDom = new DOMDocument();
		$html = htmlentities($html, ENT_NOQUOTES, 'UTF-8', false);
		$html = str_replace(['&lt;', '&gt;', '&amp;lt;', '&amp;gt;'], ['<', '>', '&lt;', '&gt;'], $html);
		$tempDom -> loadHTML($html);
		foreach ($tempDom -> getElementsByTagName('html')[0] -> childNodes as $node)
		{
			$node = $this -> dom -> importNode($node, true);
			$htmlElement -> appendChild($node);
		}
		return $htmlElement;
	}
    public function replaceInvalidTags(string $html)
    {
        $html = preg_replace_callback('/\<style(.*?)\>[\S\s]*?\<\/style\>/i', function ($array)
        {
            $outerHTML = $array[0];
            $attributes = preg_replace('/\<style(.*?)\>[\S\s]*?\<\/style\>/i', '$1', $outerHTML);
            $innerHTML = preg_replace('/\<style(.*?)\>/i', '', $outerHTML);
            $innerHTML = preg_replace('/\<\/style\>/i', '', $innerHTML);
            $outerHTML = '<style' . $attributes . '>/*' . bin2hex($innerHTML) . '*/</style>';
            return $outerHTML;
        }, $html);
        $html = preg_replace_callback('/\<script(.*?)\>[\S\s]*?\<\/script\>/i', function ($array)
        {
            $outerHTML = $array[0];
            $attributes = preg_replace('/\<script(.*?)\>[\S\s]*?\<\/script\>/i', '$1', $outerHTML);
            $innerHTML = preg_replace('/\<script(.*?)\>/i', '', $outerHTML);
            $innerHTML = preg_replace('/\<\/script\>/i', '', $innerHTML);
            $outerHTML = '<script' . $attributes . '>/*' . bin2hex($innerHTML) . '*/</script>';
            return $outerHTML;
        }, $html);
        return $html;
    }
}
class Random
{
	private function randomInt(int $min, int $max)
	{
		if (function_exists('mt_rand'))
		{
			return mt_rand($min, $max);
		}
		else
		{
			return rand($min, $max);
		}
	}
	public function int(int $min, int $max)
	{
		return $this -> randomInt($min, $max);
	}
	public function string(int $length = 10, string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
	{
		$alphabetLength = strlen($alphabet);
		$return = '';
		for ($step = 0; $step <= $length - 1; $step++)
		{
			$return .= $alphabet[$this -> randomInt(0, $alphabetLength - 1)];
		}
		return $return;	
	}
}
class Session
{
	private ?string $id = null;
	private int $minLength = 50;
	public string $cookieName = 'session';
	public int $sessionIdLength = 150;
	public function __construct(string $cookieName = 'session', int $sessionIdLength = 150)
	{
		$this -> cookieName = $cookieName;
		if ($sessionIdLength > $this -> minLength)
		{
			$this -> sessionIdLength = $sessionIdLength;
		}
		else
		{
			$this -> sessionIdLength = $this -> minLength;
		}
		$newCookie = false;
		session_name($this -> cookieName);
		session_set_cookie_params(['httponly' => true, 'secure' => true, 'samesite' => 'Strict']);
		session_set_cookie_params(time() + 60 * 60 * 24 * 366, '/');
		if (!isset($_COOKIE[$this -> cookieName]))
		{
			$this -> id = $this -> getNewId();
			session_id($this -> id);
			session_start();
			$_SESSION['sessionCookieCheckAge'] = time();
			$newCookie = true;
		}
		else
		{
			session_start();
			if (!isset($_SESSION['sessionCookieCheckAge']))
			{
				session_destroy();
				session_name($this -> cookieName);
				session_set_cookie_params(['httponly' => true, 'secure' => true, 'samesite' => 'Strict']);
				session_set_cookie_params(time() + 60 * 60 * 24 * 366, '/');
				$this -> id = $this -> getNewId();
				session_id($this -> id);
				session_start();
				$_SESSION['sessionCookieCheckAge'] = time();
				$newCookie = true;
			}
		}
		if ($newCookie == false)
		{
			$this -> id = $_COOKIE[$this -> cookieName];
			if ($this -> getAge() > 60 * 24)
			{
				$this -> newId();
			}
		}
	}
	public function getAge()
	{
		return (time() - $_SESSION['sessionCookieCheckAge']) / 60;
	}
	public function destroy()
	{
		session_destroy();
	}
	public function getId()
	{
		return $this -> id;
	}
	public function newId()
	{
		$oldSessionData = $_SESSION;
		$this -> destroy();
		session_name($this -> cookieName);
		session_set_cookie_params(['httponly' => true, 'secure' => true, 'samesite' => 'Strict']);
		session_set_cookie_params(time() + 60 * 60 * 24 * 366, '/');
		$this -> id = $this -> getNewId();
		session_id($this -> id);
		session_start();
		$_SESSION = $oldSessionData;
		$_SESSION['sessionCookieCheckAge'] = time();
	}
	private function getNewId()
	{
		$maxLength = $this -> sessionIdLength;
		$Random = new Random();
		$RandomString = $Random -> string($maxLength, 'abcdefghijklmnopqrstuvwxyz1234567890-');
		if (function_exists('session_create_id'))
		{
			$collisionFreeId = session_create_id();
			$collisionFreeId = strtolower($collisionFreeId);
			$collisionFreeId = str_replace(',', '', $collisionFreeId);
		}
		else
		{
			$collisionFreeId = '';
		}
		$id = $collisionFreeId . $RandomString;
		return substr($id, 0, $maxLength);		
	}
	public function close()
	{
		session_write_close();
	}
}
if (!function_exists('sha512'))
{
	function sha512(string $password)
	{
		return hash('sha512', $password);
	}
}
?>
