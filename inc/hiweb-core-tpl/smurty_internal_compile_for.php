<?php
/**
 * hiweb_tpl Internal Plugin Compile For
 * Compiles the {for} {forelse} {/for} tags
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile For Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_For extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {for} tag
     * hiweb_tpl 3 does implement two different syntax's:
     * - {for $var in $array}
     * For looping over arrays or iterators
     * - {for $x=0; $x<$y; $x++}
     * For general loops
     * The parser is generating different sets of attribute by which this compiler can
     * determine which syntax is used.
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        if ($parameter == 0) {
            $this->required_attributes = array('start', 'to');
            $this->optional_attributes = array('max', 'step');
        } else {
            $this->required_attributes = array('start', 'ifexp', 'var', 'step');
            $this->optional_attributes = array();
        }
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        $output = "<?php ";
        if ($parameter == 1) {
            foreach ($_attr['start'] as $_statement) {
                $output .= " \$_smurty_tpl->tpl_vars[$_statement[var]] = new Smurty_Variable;";
                $output .= " \$_smurty_tpl->tpl_vars[$_statement[var]]->value = $_statement[value];\n";
            }
            $output .= "  if ($_attr[ifexp]) { for (\$_foo=true;$_attr[ifexp]; \$_smurty_tpl->tpl_vars[$_attr[var]]->value$_attr[step]) {\n";
        } else {
            $_statement = $_attr['start'];
            $output .= "\$_smurty_tpl->tpl_vars[$_statement[var]] = new Smurty_Variable;";
            if (isset($_attr['step'])) {
                $output .= "\$_smurty_tpl->tpl_vars[$_statement[var]]->step = $_attr[step];";
            } else {
                $output .= "\$_smurty_tpl->tpl_vars[$_statement[var]]->step = 1;";
            }
            if (isset($_attr['max'])) {
                $output .= "\$_smurty_tpl->tpl_vars[$_statement[var]]->total = (int) min(ceil((\$_smurty_tpl->tpl_vars[$_statement[var]]->step > 0 ? $_attr[to]+1 - ($_statement[value]) : $_statement[value]-($_attr[to])+1)/abs(\$_smurty_tpl->tpl_vars[$_statement[var]]->step)),$_attr[max]);\n";
            } else {
                $output .= "\$_smurty_tpl->tpl_vars[$_statement[var]]->total = (int) ceil((\$_smurty_tpl->tpl_vars[$_statement[var]]->step > 0 ? $_attr[to]+1 - ($_statement[value]) : $_statement[value]-($_attr[to])+1)/abs(\$_smurty_tpl->tpl_vars[$_statement[var]]->step));\n";
            }
            $output .= "if (\$_smurty_tpl->tpl_vars[$_statement[var]]->total > 0) {\n";
            $output .= "for (\$_smurty_tpl->tpl_vars[$_statement[var]]->value = $_statement[value], \$_smurty_tpl->tpl_vars[$_statement[var]]->iteration = 1;\$_smurty_tpl->tpl_vars[$_statement[var]]->iteration <= \$_smurty_tpl->tpl_vars[$_statement[var]]->total;\$_smurty_tpl->tpl_vars[$_statement[var]]->value += \$_smurty_tpl->tpl_vars[$_statement[var]]->step, \$_smurty_tpl->tpl_vars[$_statement[var]]->iteration++) {\n";
            $output .= "\$_smurty_tpl->tpl_vars[$_statement[var]]->first = \$_smurty_tpl->tpl_vars[$_statement[var]]->iteration == 1;";
            $output .= "\$_smurty_tpl->tpl_vars[$_statement[var]]->last = \$_smurty_tpl->tpl_vars[$_statement[var]]->iteration == \$_smurty_tpl->tpl_vars[$_statement[var]]->total;";
        }
        $output .= "?>";

        $this->openTag($compiler, 'for', array('for', $compiler->nocache));
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        // return compiled code
        return $output;
    }
}

/**
 * hiweb_tpl Internal Plugin Compile Forelse Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Forelse extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {forelse} tag
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache) = $this->closeTag($compiler, array('for'));
        $this->openTag($compiler, 'forelse', array('forelse', $nocache));

        return "<?php }} else { ?>";
    }
}

/**
 * hiweb_tpl Internal Plugin Compile Forclose Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Forclose extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {/for} tag
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     *
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache) = $this->closeTag($compiler, array('for', 'forelse'));

        if ($openTag == 'forelse') {
            return "<?php }  ?>";
        } else {
            return "<?php }} ?>";
        }
    }
}
