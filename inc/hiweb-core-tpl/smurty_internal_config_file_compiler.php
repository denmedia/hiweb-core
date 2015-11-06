<?php
/**
 * hiweb_tpl Internal Plugin Config File Compiler
 * This is the config file compiler class. It calls the lexer and parser to
 * perform the compiling.
 *
 * @package    hiweb_tpl
 * @subpackage Config
 * @author     Uwe Tews
 */

/**
 * Main config file compiler class
 *
 * @package    hiweb_tpl
 * @subpackage Config
 */
class Smurty_Internal_Config_File_Compiler
{
    /**
     * Lexer object
     *
     * @var object
     */
    public $lex;

    /**
     * Parser object
     *
     * @var object
     */
    public $parser;

    /**
     * hiweb_tpl object
     *
     * @var hiweb_tpl object
     */
    public $smurty;

    /**
     * hiweb_tpl object
     *
     * @var Smurty_Internal_Config object
     */
    public $config;

    /**
     * Compiled config data sections and variables
     *
     * @var array
     */
    public $config_data = array();

    /**
     * Initialize compiler
     *
     * @param hiweb_tpl $smurty base instance
     */
    public function __construct($smurty)
    {
        $this->smurty = $smurty;
        $this->config_data['sections'] = array();
        $this->config_data['vars'] = array();
    }

    /**
     * Method to compile a hiweb_tpl template.
     *
     * @param  Smurty_Internal_Config $config config object
     *
     * @return bool                   true if compiling succeeded, false if it failed
     */
    public function compileSource(Smurty_Internal_Config $config)
    {
        /* here is where the compiling takes place. hiweb_tpl
          tags in the templates are replaces with PHP code,
          then written to compiled files. */
        $this->config = $config;
        // get config file source
        $_content = $config->source->content . "\n";
        // on empty template just return
        if ($_content == '') {
            return true;
        }
        // init the lexer/parser to compile the config file
        $lex = new Smurty_Internal_Configfilelexer($_content, $this);
        $parser = new Smurty_Internal_Configfileparser($lex, $this);

        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbEncoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        } else {
            $mbEncoding = null;
        }


        if ($this->smurty->_parserdebug) {
            $parser->PrintTrace();
        }
        // get tokens from lexer and parse them
        while ($lex->yylex()) {
            if ($this->smurty->_parserdebug) {
                echo "<br>Parsing  {$parser->yyTokenName[$lex->token]} Token {$lex->value} Line {$lex->line} \n";
            }
            $parser->doParse($lex->token, $lex->value);
        }
        // finish parsing process
        $parser->doParse(0, 0);

        if ($mbEncoding) {
            mb_internal_encoding($mbEncoding);
        }

        $config->compiled_config = '<?php $_config_vars = ' . var_export($this->config_data, true) . '; ?>';
    }

    /**
     * display compiler error messages without dying
     * If parameter $args is empty it is a parser detected syntax error.
     * In this case the parser is called to obtain information about expected tokens.
     * If parameter $args contains a string this is used as error message
     *
     * @param string $args individual error message or null
     *
     * @throws SmurtyCompilerException
     */
    public function trigger_config_file_error($args = null)
    {
        $this->lex = Smurty_Internal_Configfilelexer::instance();
        $this->parser = Smurty_Internal_Configfileparser::instance();
        // get template source line which has error
        $line = $this->lex->line;
        if (isset($args)) {
            // $line--;
        }
        $match = preg_split("/\n/", $this->lex->data);
        $error_text = "Syntax error in config file '{$this->config->source->filepath}' on line {$line} '{$match[$line - 1]}' ";
        if (isset($args)) {
            // individual error message
            $error_text .= $args;
        } else {
            // expected token from parser
            foreach ($this->parser->yy_get_expected_tokens($this->parser->yymajor) as $token) {
                $exp_token = $this->parser->yyTokenName[$token];
                if (isset($this->lex->smurty_token_names[$exp_token])) {
                    // token type from lexer
                    $expect[] = '"' . $this->lex->smurty_token_names[$exp_token] . '"';
                } else {
                    // otherwise internal token name
                    $expect[] = $this->parser->yyTokenName[$token];
                }
            }
            // output parser error message
            $error_text .= ' - Unexpected "' . $this->lex->value . '", expected one of: ' . implode(' , ', $expect);
        }
        throw new SmurtyCompilerException($error_text);
    }
}
