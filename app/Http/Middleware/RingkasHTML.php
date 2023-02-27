<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RingkasHTML
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ((strtolower(strtok($response->headers->get('Content-Type'), ';')) === 'text/html')
        //  || (is_object($response) && $response instanceof Response)
         )
        {
            $html = $response->getContent();

            $iniData  = [];
            $iniData['pcre.recursion_limit'] = ini_get('pcre.recursion_limit');
            $iniData['zlib.output_compression'] = ini_get('zlib.output_compression');
            $iniData['zlib.output_compression_level'] = ini_get('zlib.output_compression_level');

            // ini_set('pcre.recursion_limit', '16777');
            // // Some browser cant get content type.
            // ini_set('zlib.output_compression', '4096');
            // // Let server decide.
            // ini_set('zlib.output_compression_level', '-1');

            $regexRemoveWhiteSpace = '%(?>[^\S ]\s*| \s{2,})(?=(?:(?:[^<]++| <(?!/?(?:textarea|pre)\b))*+)(?:<(?>textarea|pre)\b|\z))%ix';
            $new_buffer = preg_replace($regexRemoveWhiteSpace, '', $html);

            // $html = config('app.debug') ? $html : $this->compressJscript($html);

            // $html = config('app.debug') ? $html : preg_replace(array_keys($allRules),array_values($allRules),$html);

            // $html = $this->compress($html);
    
            $response->setContent($new_buffer);
            
            // ini_set('pcre.recursion_limit', $iniData['pcre.recursion_limit']);
            // ini_set('zlib.output_compression', $iniData['zlib.output_compression']);
            // ini_set('zlib.output_compression_level', $iniData['zlib.output_compression_level']);
        }
        
        return $response;
    }

    public function compressJscript($html)
    {
        // JavaScript compressor by John Elliot <jj5@jj5.net>
        $replace = [
            '#\'([^\n\']*?)/\*([^\n\']*)\'#' => "'\1/'+\'\'+'*\2'",
            // remove comments from ' strings
            '#\"([^\n\"]*?)/\*([^\n\"]*)\"#' => '"\1/"+\'\'+"*\2"',
            // remove comments from " strings
            '#/\*.*?\*/#s' => "",// strip C style comments
            '#[\r\n]+#' => "\n",
            // remove blank lines and \r's
            '#\n([ \t]*//.*?\n)*#s' => "\n",
            // strip line comments (whole line only)
            '#([^\\])//([^\'"\n]*)\n#s' => "\\1\n",
            // strip line comments
            // (that aren't possibly in strings or regex's)
            '#\n\s+#' => "\n",// strip excess whitespace
            '#\s+\n#' => "\n",// strip excess whitespace
            '#(//[^\n]*\n)#s' => "\\1\n",
            // extra line feed after any comments left
            // (important given later replacements)
            '#/([\'"])\+\'\'\+([\'"])\*#' => "/*"
            // restore comments in strings
        ];

        $script = preg_replace(array_keys($replace), $replace, $html);
        
        $replace = [
            "&&\n" => "&&",
            "||\n" => "||",
            "(\n" => "(",
            ")\n" => ")",
            "[\n" => "[",
            "]\n" => "]",
            "+\n" => "+",
            ",\n" => ",",
            "?\n" => "?",
            ":\n" => ":",
            ";\n" => ";",
            "{\n" => "{",
            //  "}\n"  => "}", (because I forget to put semicolons after function assignments)
            "\n]" => "]",
            "\n)" => ")",
            "\n}" => "}",
            "\n\n" => "\n",
            "false" => "0",
            "true" => "1",
        ];
        $script = str_replace(array_keys($replace), $replace, $script);

        return trim($script);
    }

    public static function compressOld($html)
    {
        $whiteSpaceRules = [
            '/(\s)+/s' => '\\1',// shorten multiple whitespace sequences
            "#>\s+<#" => ">\n<",  // Strip excess whitespace using new line
            "#\n\s+<#" => "\n<",// strip excess whitespace using new line
            '/\>[^\S ]+/s' => '>',
            // Strip all whitespaces after tags, except space
            '/[^\S ]+\</s' => '<',// strip whitespaces before tags, except space
            /**
             * '/\s+     # Match one or more whitespace characters
             * (?!       # but only if it is impossible to match...
             * [^<>]*   # any characters except angle brackets
             * >        # followed by a closing bracket.
             * )         # End of lookahead
             * /x',
             */

            //Remove all whitespaces except content between html tags.
            //MOST DANGEROUS
            //            '/\s+(?![^<>]*>)/x' => '',
        ];
        $commentRules = [
            "/<!--.*?-->/ms" => '',// Remove all html comment.,
        ];
        $replaceWords = [
            //OldWord will be replaced by the NewWord
            // OldWord <-> NewWord DO NOT REMOVE THIS LINE. {REFERENCE LINE}
            //'/\bOldWord\b/i' =>'NewWord'
        ];
        $allRules = array_merge(
            $replaceWords,
            $commentRules,
            $whiteSpaceRules
        );
        
        $new_buffer = preg_replace(array_keys($allRules),array_values($allRules),$html);

        if ($new_buffer === null) {
            $new_buffer = $html;
        }

        return $new_buffer;
    }
}
