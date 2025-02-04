<?php
namespace Cetera\Twig\Node; 

class Widget extends \Twig\Node\Node implements \Twig\Node\NodeOutputInterface
{
    public function __construct(\Twig\Node\Expression\AbstractExpression $expr, \Twig\Node\Expression\AbstractExpression $variables, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr, 'variables' => $variables), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig\Compiler $compiler)
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
                //->write("echo '<div class=\"callout alert\">'.\$e->getMessage().'</div>';\n")
				->write("echo '<!-- '.\$e->getMessage().' '.\$e->getFile().' '.\$e->getLine().' -->';\n")
                ->outdent()
                ->write("}\n\n")
        ;		 

    }
}
