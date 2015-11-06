<?php
/**
 * hiweb_tpl Internal Plugin Compile Foreach
 * Compiles the {foreach} {foreachelse} {/foreach} tags
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 * @author     Uwe Tews
 */

/**
 * hiweb_tpl Internal Plugin Compile Foreach Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Foreach extends Smurty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $required_attributes = array('from', 'item');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $optional_attributes = array('name', 'key');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smurty_Internal_CompileBase
     */
    public $shorttag_order = array('from', 'item', 'key', 'name');

    /**
     * Compiles code for the {foreach} tag
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

        $from = $_attr['from'];
        $item = $_attr['item'];
        if (!strncmp("\$_smurty_tpl->tpl_vars[$item]", $from, strlen($item) + 24)) {
            $compiler->trigger_template_error("item variable {$item} may not be the same variable as at 'from'", $compiler->lex->taglineno);
        }

        if (isset($_attr['key'])) {
            $key = $_attr['key'];
        } else {
            $key = null;
        }

        $this->openTag($compiler, 'foreach', array('foreach', $compiler->nocache, $item, $key));
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;

        if (isset($_attr['name'])) {
            $name = $_attr['name'];
            $has_name = true;
            $SmurtyVarName = '$smurty.foreach.' . trim($name, '\'"') . '.';
        } else {
            $name = null;
            $has_name = false;
        }
        $ItemVarName = '$' . trim($item, '\'"') . '@';
        // evaluates which hiweb_tpl variables and properties have to be computed
        if ($has_name) {
            $usesSmurtyFirst = strpos($compiler->lex->data, $SmurtyVarName . 'first') !== false;
            $usesSmurtyLast = strpos($compiler->lex->data, $SmurtyVarName . 'last') !== false;
            $usesSmurtyIndex = strpos($compiler->lex->data, $SmurtyVarName . 'index') !== false;
            $usesSmurtyIteration = strpos($compiler->lex->data, $SmurtyVarName . 'iteration') !== false;
            $usesSmurtyShow = strpos($compiler->lex->data, $SmurtyVarName . 'show') !== false;
            $usesSmurtyTotal = strpos($compiler->lex->data, $SmurtyVarName . 'total') !== false;
        } else {
            $usesSmurtyFirst = false;
            $usesSmurtyLast = false;
            $usesSmurtyTotal = false;
            $usesSmurtyShow = false;
        }

        $usesPropFirst = $usesSmurtyFirst || strpos($compiler->lex->data, $ItemVarName . 'first') !== false;
        $usesPropLast = $usesSmurtyLast || strpos($compiler->lex->data, $ItemVarName . 'last') !== false;
        $usesPropIndex = $usesPropFirst || strpos($compiler->lex->data, $ItemVarName . 'index') !== false;
        $usesPropIteration = $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'iteration') !== false;
        $usesPropShow = strpos($compiler->lex->data, $ItemVarName . 'show') !== false;
        $usesPropTotal = $usesSmurtyTotal || $usesSmurtyShow || $usesPropShow || $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'total') !== false;
        // generate output code
        $output = "<?php ";
        $output .= " \$_smurty_tpl->tpl_vars[$item] = new Smurty_Variable; \$_smurty_tpl->tpl_vars[$item]->_loop = false;\n";
        if ($key != null) {
            $output .= " \$_smurty_tpl->tpl_vars[$key] = new Smurty_Variable;\n";
        }
        $output .= " \$_from = $from; if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array');}\n";
        if ($usesPropTotal) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->total= \$_smurty_tpl->_count(\$_from);\n";
        }
        if ($usesPropIteration) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->iteration=0;\n";
        }
        if ($usesPropIndex) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->index=-1;\n";
        }
        if ($usesPropShow) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->show = (\$_smurty_tpl->tpl_vars[$item]->total > 0);\n";
        }
        if ($has_name) {
            if ($usesSmurtyTotal) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['total'] = \$_smurty_tpl->tpl_vars[$item]->total;\n";
            }
            if ($usesSmurtyIteration) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['iteration']=0;\n";
            }
            if ($usesSmurtyIndex) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['index']=-1;\n";
            }
            if ($usesSmurtyShow) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['show']=(\$_smurty_tpl->tpl_vars[$item]->total > 0);\n";
            }
        }
        $output .= "foreach (\$_from as \$_smurty_tpl->tpl_vars[$item]->key => \$_smurty_tpl->tpl_vars[$item]->value) {\n\$_smurty_tpl->tpl_vars[$item]->_loop = true;\n";
        if ($key != null) {
            $output .= " \$_smurty_tpl->tpl_vars[$key]->value = \$_smurty_tpl->tpl_vars[$item]->key;\n";
        }
        if ($usesPropIteration) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->iteration++;\n";
        }
        if ($usesPropIndex) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->index++;\n";
        }
        if ($usesPropFirst) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->first = \$_smurty_tpl->tpl_vars[$item]->index === 0;\n";
        }
        if ($usesPropLast) {
            $output .= " \$_smurty_tpl->tpl_vars[$item]->last = \$_smurty_tpl->tpl_vars[$item]->iteration === \$_smurty_tpl->tpl_vars[$item]->total;\n";
        }
        if ($has_name) {
            if ($usesSmurtyFirst) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['first'] = \$_smurty_tpl->tpl_vars[$item]->first;\n";
            }
            if ($usesSmurtyIteration) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['iteration']++;\n";
            }
            if ($usesSmurtyIndex) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['index']++;\n";
            }
            if ($usesSmurtyLast) {
                $output .= " \$_smurty_tpl->tpl_vars['smurty']->value['foreach'][$name]['last'] = \$_smurty_tpl->tpl_vars[$item]->last;\n";
            }
        }
        $output .= "?>";

        return $output;
    }
}

/**
 * hiweb_tpl Internal Plugin Compile Foreachelse Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Foreachelse extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {foreachelse} tag
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

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('foreach'));
        $this->openTag($compiler, 'foreachelse', array('foreachelse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smurty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}

/**
 * hiweb_tpl Internal Plugin Compile Foreachclose Class
 *
 * @package    hiweb_tpl
 * @subpackage Compiler
 */
class Smurty_Internal_Compile_Foreachclose extends Smurty_Internal_CompileBase
{
    /**
     * Compiles code for the {/foreach} tag
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

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('foreach', 'foreachelse'));

        return "<?php } ?>";
    }
}
