<?php

namespace Mudi\Service;

class CssUsageService
{	
	protected $cssList;
	protected $parser;
	protected $result;

	public function __construct()
	{

		$this->name = 'css-usage';
		$this->result = new \Mudi\Result\CssUsageResult();
	}


	public function getUsage($file_path)
	{
		try{
			$this->parser = new \Sabberworm\CSS\Parser(file_get_contents($file_path));
			$doc = $this->parser->parse();			
		}
		catch(\Exception $e)
		{
			$this->result->errors[] = $e->getMessage();
			return $this->result;
		}

		$this->cssList = $doc->getContents(); 

		$this->countMediaQueries();
		$this->getCSS3();

		$this->result->css3_rules = array_unique($this->result->css3_rules);

		return $this->result;

	}


	protected function getCSS3()
	{
		foreach($this->cssList as $block)
		{
			//var_dump(get_class($block));

			$current_css_rules 		= array(); //on stocke toutes les règles parcourues
			$current_css3_rules 	= array(); //on stocke uniquement les CSS3 rules
			$css3_property_list 	= $this->getCSS3PropertyList(); //liste propriétés CSS3

			if($block instanceof \Sabberworm\CSS\RuleSet\DeclarationBlock)
			{
				$current_block = $block;

				foreach($block->getRules() as $rule)
				{
					$rule = $rule->getRule();

					if(in_array($rule, $css3_property_list))
					{	
						$current_css3_rules[] = $rule;
						$this->result->css3_count++;
					}
					else
					{
						$current_css_rules[] = $rule;
					}
				}

				//vendors prefixes check
				if(!empty($current_css3_rules))
				{
					foreach($current_css3_rules as $css3_rule)
					{
							//si manque un des deux, on considère manquant
						$moz_rule 		= '-moz-' . $css3_rule;
						$webkit_rule 	= '-webkit-' .$css3_rule;

						if(!in_array($moz_rule, $current_css_rules) || !in_array($webkit_rule, $current_css_rules))
						{
							$this->result->css3_no_vendor++;
						}
					}
					$this->result->css3_rules = array_merge($this->result->css3_rules, $current_css3_rules);
				}
			}
		}		
	}

	protected function countMediaQueries()
	{
		foreach($this->cssList as $block)
		{
			if($block instanceof \Sabberworm\CSS\CSSList\AtRuleBlockList)
			{
				$name = $block->atRuleName();
				if($name === 'media'){
					$this->result->media_queries[] = $block->atRuleArgs();
					$this->result->media_query_count++;
				}
			}
		}
	}


	protected function getCSS3PropertyList()
	{
		return array(
			"alignment-adjust",
			"alignment-baseline",
			"@keyframes",
			"animation",
			"animation-name",
			"animation-duration",
			"animation-timing-function",
			"animation-delay",
			"animation-iteration-count",
			"animation-direction",
			"animation-play-state",
			"appearance",
			"backface-visibility",
			"background-clip",
			"background-origin",
			"background-size",
			"baseline-shift",
			"bookmark-label",
			"bookmark-level",
			"bookmark-target",
			"border-bottom-left-radius",
			"border-bottom-right-radius",
			"border-image",
			"border-image-outset",
			"border-image-repeat",
			"border-image-slice",
			"border-image-source",
			"border-image-width",
			"border-radius",
			"border-top-left-radius",
			"border-top-right-radius",
			"box-decoration-break",
			"box-align",
			"box-direction",
			"box-flex",
			"box-flex-group",
			"box-lines",
			"box-ordinal-group",
			"box-orient",
			"box-pack",
			"box-shadow",
			"box-sizing",
			"color-profile",
			"column-fill",
			"column-gap",
			"column-rule",
			"column-rule-color",
			"column-rule-style",
			"column-rule-width",
			"column-span",
			"column-width",
			"columns",
			"column-count",
			"crop",
			"dominant-baseline",
			"drop-initial-after-adjust",
			"drop-initial-after-align",
			"drop-initial-before-adjust",
			"drop-initial-before-align",
			"drop-initial-size",
			"drop-initial-value",
			"fit",
			"fit-position",
			"float-offset",
			"@font-face",
			"font-size-adjust",
			"font-stretch",
			"grid-columns",
			"grid-rows",
			"hanging-punctuation",
			"hyphenate-after",
			"hyphenate-before",
			"hyphenate-characters",
			"hyphenate-lines",
			"hyphenate-resource",
			"hyphens",
			"icon",
			"image-orientation",
			"image-resolution",
			"inline-box-align",
			"line-stacking",
			"line-stacking-ruby",
			"line-stacking-shift",
			"line-stacking-strategy",
			"mark",
			"mark-after",
			"mark-before",
			"marks",
			"marquee-direction",
			"marquee-play-count",
			"marquee-speed",
			"marquee-style",
			"move-to",
			"nav-down",
			"nav-index",
			"nav-left",
			"nav-right",
			"nav-up",
			"opacity",
			"outline-offset",
			"overflow-style",
			"overflow-x",
			"overflow-y",
			"page",
			"page-policy",
			"perspective",
			"perspective-origin",
			"punctuation-trim",
			"rendering-intent",
			"resize",
			"rest",
			"rest-after",
			"rest-before",
			"rotation",
			"rotation-point",
			"ruby-align",
			"ruby-overhang",
			"ruby-position",
			"ruby-span",
			"size",
			"string-set",
			"target",
			"target-name",
			"target-new",
			"target-position",
			"text-align-last",
			"text-emphasis",
			"text-height",
			"text-justify",
			"text-outline",
			"text-overflow",
			"text-shadow",
			"text-wrap",
			"transform",
			"transform-origin",
			"transform-style",
			"transition",
			"transition-property",
			"transition-duration",
			"transition-timing-function",
			"transition-delay",
			"word-break",
			"word-wrap"
			)
;
}

}


