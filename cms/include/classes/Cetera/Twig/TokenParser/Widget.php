<?php
namespace Cetera\Twig\TokenParser; 

class Widget extends \Twig\TokenParser\AbstractTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(\Twig\Token $token)
    {
        $expr = $this->parser->parseExpression();

        $variables = $this->parseArguments();

        return new \Cetera\Twig\Node\Widget($expr, $variables, $token->getLine(), $this->getTag());
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

		$variables = new \Twig\Node\Expression\ArrayExpression( [],0 );
        if ($stream->nextIf(\Twig\Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->parseExpression();
        }

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return $variables;
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'widget';
    }
}
