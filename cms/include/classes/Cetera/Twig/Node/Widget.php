<?php
namespace Cetera\Twig\Node;

use Twig\Attribute\YieldReady;

#[YieldReady]
class Widget extends \Twig\Node\Node implements \Twig\Node\NodeOutputInterface
{
    public function __construct(\Twig\Node\Expression\AbstractExpression $expr, \Twig\Node\Expression\AbstractExpression $variables, $lineno)
    {
        parent::__construct(array('expr' => $expr, 'variables' => $variables), array(), $lineno);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig\Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);
		
        $compiler->write("try {\n")->indent();		
		
        $compiler
             ->write("\Cetera\Application::getInstance()->getWidget(")
             ->subcompile($this->getNode('expr'));
			 
		if ($this->getNode('variables'))
		{
			 $compiler
				 ->raw(', ')
				 ->subcompile($this->getNode('variables'));
		}
				 
        $compiler->raw(")->display();\n");	

        $compiler
                ->outdent()
                ->write("} catch (\\Exception \$e) {\n")
                ->indent()
                ->write("yield \Cetera\Widget\Widget::errorHtml(\$e);\n")
                ->outdent()
                ->write("}\n\n")
        ;		 

    }
}
