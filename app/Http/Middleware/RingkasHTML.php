<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;

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

            $html = trim(preg_replace([
                // t = text
                // o = tag open
                // c = tag close
                // Keep important white-space(s) after self-closing HTML tag(s)
                '#<(img|input)(>| .*?>)#s',
                // Remove a line break and two or more white-space(s) between tag(s)
                '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s',
                // t+c || o+t
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s',
                // o+o || c+c
                '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s',
                // c+t || t+o || o+t -- separated by long white-space(s)
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s',
                // empty tag
                '#<(img|input)(>| .*?>)<\/\1>#s',
                // reset previous fix
                '#(&nbsp;)&nbsp;(?![<\s])#',
                // clean up ...
                '#(?<=\>)(&nbsp;)(?=\<)#',
                // --ibid
                '/\s+/',
            ],[
                '<$1$2</$1>',
                '$1$2$3',
                '$1$2$3',
                '$1$2$3$4$5',
                '$1$2$3$4$5$6$7',
                '$1$2$3',
                '<$1$2',
                '$1 ',
                '$1',
                " ",
            ], $html));
    
            $response->setContent($html);
            
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
