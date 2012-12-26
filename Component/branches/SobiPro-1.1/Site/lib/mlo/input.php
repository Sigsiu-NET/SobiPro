<?php
/**
 * @version: $Id: input.php 2318 2012-03-27 12:03:46Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-03-27 14:03:46 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2318 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/mlo/input.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
/**
 * @author Radek Suski
 * @version 1.0
 * @since 1.0
 * @created 11-Jan-2009 12:45:28 PM
 */
abstract class SPHtml_Input
{
	/**
	 * @param mixed $params
	 */
	private static function checkArray( &$params )
	{
		if ( $params && is_string( $params ) ) {
			$class = SPLoader::loadClass( 'types.array' );
			$arr = new $class;
			$arr->fromString( $params, ',', '=' );
			$params = $arr->toArr();
		}
		$p = array();
		if ( is_array( $params ) ) {
			foreach ( $params as $i => $k ) {
				$p[ trim( $i ) ] = /*trim*/
						( $k );
			}
		}
		$params = is_array( $p ) ? $p : array();
	}

	/**
	 * @param string $txt
	 */
	private static function translate( $txt )
	{
		if ( strstr( $txt, 'translate:' ) ) {
			$matches = array();
			preg_match( '/translate\:\[(.*)\]/', $txt, $matches );
			$txt = str_replace( $matches[ 0 ], Sobi::Txt( $matches[ 1 ] ), $txt );
		}
		return Sobi::Clean( $txt );
	}

	/**
	 * @param array $params
	 * @return strring
	 */
	private static function params( $params )
	{
		$add = null;
		self::checkArray( $params );
		if ( $params && is_array( $params ) && count( $params ) ) {
			foreach ( $params as $param => $v ) {
				if ( in_array( $param, array( 'required' ) ) ) {
					continue;
				}
				$v = trim( str_replace( '"', '\'', $v ) );
				$param = str_replace( array( '\'', '"' ), null, trim( $param ) );
				$add .= " {$param}=\"{$v}\"";
			}
		}
		return $add;
	}

	/**
	 * Creates simple file field
	 *
	 * @param string $name - name of the html field
	 * @param int $size - field size
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string $accept - accepted file types
	 * @return string
	 */
	public static function file( $name, $size = 50, $params = null, $accept = '*' )
	{
		$params = self::params( $params );
		$f = "\n<input name=\"{$name}\" type=\"file\" size=\"{$size}\" value=\"\" accept=\"{$accept}\"{$params}/>\n";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- FileBox '{$name}' Output -->{$f}<!-- FileBox '{$name}' End -->\n\n";
	}


	/**
	 * Creates ajax file field
	 *
	 * @param string $name - name of the html field
	 * @param string $accept - accepted file types
	 * @param string $value - possible value for the inbox
	 * @param string $class - class name
	 * @param string $task - task override
	 * @param array $scripts - custom JavaScript files
	 * @param array $request - custom request
	 * @return string
	 */
	public static function fileUpload( $name, $accept = '*', $value = null, $class = 'spFileUpload', $task = 'file.upload', $scripts = array( 'jquery', 'jquery-form', 'fileupload' ), $request = null )
	{
		if ( is_string( $scripts ) ) {
			$scripts = SPFactory::config()->structuralData( $scripts );
		}
		SPFactory::header()->addJsFile( $scripts );
		if ( !( $request ) ) {
			$request = array(
				'option' => 'com_sobipro',
				'task' => $task,
				'sid' => Sobi::Section(),
				'ident' => $name . '-file',
				SPFactory::mainframe()->token() => 1,
				'format' => 'raw'
			);
		}
		$f = null;
		$f .= "<div class=\"{$class}\">";
		$f .= '<div class="file">';
		$f .= self::file( $name . '-file', 0, array( 'class' => 'spFileUpload hide' ), $accept );
		$f .= '</div>';
		$f .= "<input type=\"text\" readonly=\"readonly\" class=\"input-xlarge selected pull-left\" value=\"{$value}\"/>";
		$f .= '<div class="btn-group">';
		$f .= '<button class="btn select" type="button"><i class="icon-eye-open"></i>&nbsp;' . Sobi::Txt( 'UPLOAD_SELECT' ) . '</button>';
		$f .= '<button class="btn remove" disabled="disabled" type="button">' . '&nbsp;<i class="icon-remove"></i></button>';
		$f .= '<button class="btn upload" disabled="disabled" type="button" rel=\'' . json_encode( $request ) . '\'>' . Sobi::Txt( 'START_UPLOAD' ) . '&nbsp;<i class="icon-upload-alt"></i></button>';
		$f .= '</div>';
		$f .= '<div class="hide progress-container">';
		$f .= '<div class="progress progress-success"><div class="bar"></div></div>';
		$f .= '<span class="progress-message badge badge-success pull-left"></span>';
		$f .= '</div>';
		$f .= '<div class="alert hide"><button type="button" class="close" data-dismiss="alert">×</button><div>&nbsp;</div></div>';
		$f .= "<input type=\"hidden\" name=\"{$name}\" value=\"\" class='idStore'/>";
		$f .= '</div>';
		return $f;
	}

	/**
	 * Creates a HTML file box
	 *
	 * @param string $name - name of the html field
	 * @param string $value - selected value
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @return string
	 */
	public static function text( $name, $value = null, $params = null )
	{
		$params = self::params( $params );
		$value = strlen( $value ) ? str_replace( '"', '&quot;', SPLang::entities( $value, true ) ) : null;
		$f = "\n<input type=\"text\" name=\"{$name}\" value=\"{$value}\"{$params}/>\n";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- InputBox '{$name}' Output -->{$f}<!-- InputBox '{$name}' End -->\n\n";
	}

	/**
	 * Creates simple HTML SubmitButton
	 *
	 * @param string $name - name of the html field
	 * @param string $value - selected value
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @return string
	 */
	public static function submit( $name, $value = null, $params = null )
	{
		$params = self::params( $params );
		$value = self::translate( $value );
		$value = strlen( $value ) ? SPLang::entities( /*Sobi::Txt*/
			( $value ), true ) : null;
		$f = "\n<input type=\"submit\" name=\"{$name}\" value=\"{$value}\"{$params}/>\n";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- SubmitButton '{$name}' Output -->{$f}<!-- SubmitButton '{$name}' End -->\n\n";
	}

	/**
	 * Displays a hidden token field
	 * @return    string
	 */
	public static function token()
	{
		return '<input type="hidden" name="' . SPFactory::mainframe()->token() . '" value="1" />';
	}

	/**
	 * Creates simple HTML SubmitButton
	 *
	 * @param string $name - name of the html field
	 * @param string $value - selected value
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @return string
	 */
	public static function button( $name, $value = null, $params = null )
	{
		self::checkArray( $params );
		$f = null;
		$h = null;
		// bootstrap modal needs an href
		if ( isset( $params[ 'href' ] ) && !( strstr( $params[ 'href' ], '#' ) ) ) {
			SPFactory::header()->addJsCode( "
				function {$name}Redirect()
				{
					window.location ='{$params[ 'href' ]}';
					return false;
				}
			" );
			$params[ 'href' ] = htmlentities( $params[ 'href' ] );
			// sending data twice in payment screen
//			$a = "\n<a href=\"{$params[ 'href' ]}\" class=\"spDisabled\">";
//			$a = "\n<a href=\"#\" class=\"spDisabled\">";
			$h = " onclick=\"{$name}Redirect()\"";
			unset( $params[ 'href' ] );
		}
		$params = self::params( $params );
		$value = self::translate( $value );
		$f = "\n<button type=\"button\" name=\"{$name}\" {$h}{$params}>{$value}</button>\n";
		if ( isset( $a ) ) {
			$f = $a . $f . "</a>\n";
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- Button '{$name}' Output --> {$f}<!-- Button '{$name}' End -->\n\n";
	}

	/**
	 * Creates a textarea field with or without WYSIWYG editor
	 *
	 * @param string $name - name of the html field
	 * @param string $value - selected value
	 * @param bool $editor - enables WYSIWYG editor
	 * @param int $width - width of the created textarea field in pixel
	 * @param int $height - height of the created textarea field in pixel
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string $image - url of an image
	 * @return string
	 */
	public static function textarea( $name, $value = null, $editor = false, $width = 550, $height = 350, $params = '' )
	{
		self::checkArray( $params );
		if ( !isset( $params[ 'style' ] ) ) {
			$params[ 'style' ] = "width: {$width}px; height: {$height}px;";
		}
		Sobi::Trigger( 'BeforeCreateField', ucfirst( __FUNCTION__ ), array( &$name, &$value, &$editor, &$width, &$height, &$params ) );
		$value = SPLang::entities( $value );
		if ( $editor ) {
			$e = Sobi::Cfg( 'html.editor', 'cms.html.editor' );
			$c = SPLoader::loadClass( $e );
			if ( $c ) {
				$e = new $c();
				$area = $e->display( $name, $value, $width, $height, ( boolean )Sobi::Cfg( 'html.editor_buttons', true ), $params = array() );
			}
		}
		else {
			$params = self::params( $params );
			$area = "<textarea name=\"{$name}\" {$params}>{$value}</textarea>";
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$area ) );
		return "\n<!-- TextArea '{$name}' Output -->\n{$area}\n<!-- TextArea '{$name}' End -->\n\n";
	}

	/**
	 * Creates single radio button
	 *
	 * example
	 *
	 * SPHtml_Input::radio( 'myfield', 'myvalue', 'translate:[langsection.langconstant]', 'myid', false, array( 'class' => 'inputbox' ) )
	 *
	 * @param string $name - name of the html field
	 * @param string $value - values of the html field
	 * @param string $label - label to display beside the field.
	 * @param string $id - id of the field
	 * @param bool $checked - is selected or not / or string $checked the checked value
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param array $order - on which site from the label the field should be displayed and on which the image
	 * @param string $image - url of an image
	 * @return string
	 */
	public static function radio( $name, $value, $label = null, $id = null, $checked = false, $params = null, $order = array( 'field', 'image', 'label' ), $image = null )
	{
		$params = self::params( $params );
		if ( !( is_bool( $checked ) ) ) {
			$checked = ( ( string )$checked == ( string )$value ) ? true : false;
		}
		$label = $label ? self::translate( $label ) : null;
		$checked = $checked ? " checked=\"checked\" " : null;
		$$name = self::cleanOpt( $name );
		$value = self::cleanOpt( $value );
		$f = "\n<input type=\"radio\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\"{$checked}{$params}/>";
		$l = $label ? "\n<label for=\"{$id}\">{$label}</label>" : null;
		if ( $image ) {
			$image = "\n<img src=\"{$image}\" alt=\"{$label}\"/>";
		}
		if ( is_array( $order ) ) {
			$field = null;
			foreach ( $order as $position ) {
				switch ( $position ) {
					case 'field':
						$field .= $f;
						break;
					case 'label':
						$field .= $l;
						break;
					case 'image':
						$field .= $image;
						break;
				}
			}
			$f = $field;
		}
		else {
			$f = ( $order == 'left' ) ? $l . $f : $f . $l;
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- RadioButton '{$name}' Output -->{$f}\n<!-- RadioButton '{$name}' End -->\n";
	}

	/**
	 * Cretaes group of check boxes
	 *
	 * example:
	 *
	 * SPHtml_Input::checkBoxGroup( 'myfield', array( 'translate:[enabled]' => 1, 'translate:[disabled]' => 0 ), 'myid', 1 )
	 *
	 * @param string $name - name of the html field
	 * @param array $values - two-dimensional array with values and their labels. array( 'enabled' => 1, 'disabled' => 0 )
	 * @param string $id - id prefix of the field
	 * @param array $selected - two-dimensional array with values and their labels. array( 'enabled' => 1, 'disabled' => 0 )
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string $field - on which site from the label the field should be displayed
	 * @param bool $asArray - returns array instead of a string
	 * @return string
	 */
	public static function checkBoxGroup( $name, $values, $id, $selected = null, $params = null, $order = array( 'field', 'image', 'label' ), $asArray = false )
	{
		self::checkArray( $values );
		if ( $selected !== null && !( is_array( $selected ) ) ) {
			$selected = array( ( string )$selected );
		}
		elseif ( !( is_array( $selected ) ) ) {
			$selected = array();
		}
		$list = array();
		if ( count( $values ) ) {
			foreach ( $values as $value => $label ) {
				$checked = in_array( ( string )$value, $selected, true ) ? true : false;
				if ( is_array( $label ) ) {
					$image = $label[ 'image' ];
					$value = $label[ 'label' ];
				}
				else {
					$image = null;
				}
				$list[ ] = '<span>' . self::checkbox( $name . '[]', $value, $label, $id . '_' . $value, $checked, $params, $order, $image ) . '</span>';
			}
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$list ) );
		return $asArray ? $list : ( count( $list ) ? implode( "\n", $list ) : null );
	}

	/**
	 * Creates single radio button
	 *
	 * example
	 *
	 * SPHtml_Input::checkbox( 'myfield', 'myvalue', 'translate:[langsection.langconstant]', 'myid', false, array( 'class' => 'inputbox' ) )
	 *
	 * @param string $name - name of the html field
	 * @param string $value - values of the html field
	 * @param string $label - label to display beside the field.
	 * @param string $id - id of the field
	 * @param bool $checked - is selected or not / or string $checked the checked value
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param array $order - on which site from the label the field should be displayed and on which the image
	 * @return string
	 */
	public static function checkbox( $name, $value, $label = null, $id = null, $checked = false, $params = null, $order = array( 'field', 'image', 'label' ), $image = null )
	{
		$params = self::params( $params );
		if ( !( is_bool( $checked ) ) ) {
			$checked = ( ( string )$checked == ( string )$value ) ? true : false;
		}
		$label = $label ? self::cleanOpt( self::translate( $label ) ) : null;
		$checked = $checked ? " checked=\"checked\" " : null;
		$ids = $id ? "id=\"{$id}\" " : $id;
		$$name = self::cleanOpt( $name );
		$value = self::cleanOpt( $value );
		$f = "\n<input type=\"checkbox\" name=\"{$name}\" {$ids}value=\"{$value}\"{$checked}{$params}/>";
		$l = $label ? "\n<label for=\"{$id}\">{$label}</label>" : null;
		if ( $image ) {
			$image = "\n<img src=\"{$image}\" alt=\"{$label}\"/>";
		}
		if ( is_array( $order ) ) {
			$field = null;
			foreach ( $order as $position ) {
				switch ( $position ) {
					case 'field':
						$field .= $f;
						break;
					case 'label':
						$field .= $l;
						break;
					case 'image':
						$field .= $image;
						break;
				}
			}
			$f = $field;
		}
		else {
			$f = ( $order == 'left' ) ? $l . $f : $f . $l;
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- CheckBox '{$name}' Output -->{$f}\n<!-- CheckBox '{$name}' End -->\n";
	}

	/**
	 * Cretaes list of radio boxes
	 *
	 * example:
	 *
	 * SPHtml_Input::radioList( 'myfield', array( 'translate:[enabled]' => 1, 'translate:[disabled]' => 0 ), 'myid', 1 )
	 *
	 * @param string $name - name of the html field
	 * @param array $values - two-dimensional array with values and their labels. array( 'enabled' => 1, 'disabled' => 0 )
	 * @param string $id - id prefix of the field
	 * @param string $checked - value of the selected field
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string $field - on which site from the label the field should be displayed
	 * @param bool $asArray - returns array instead of a string
	 * @return string
	 */
	public static function radioList( $name, $values, $id, $checked = null, $params = null, $field = 'left', $asArray = false )
	{
		self::checkArray( $values );
		$list = array();
		if ( count( $values ) ) {
			foreach ( $values as $value => $label ) {
				if ( is_numeric( $value ) ) {
					$Id = $id . '_' . ( $value == 1 ? 'yes' : ( $value == 0 ? 'no' : $value ) );
				}
				else {
					$Id = $id . '_' . $value;
				}
				$list[ ] = '<span>' . self::radio( $name, $value, $label, $Id, $checked, $params, $field ) . '</span>';
			}
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$list ) );
		return $asArray ? $list : ( count( $list ) ? implode( "\n", $list ) : null );
	}

	/**
	 * Creates a select list
	 *
	 *
	 * example: list with multiple select options
	 *
	 * SPHtml_Input::select(
	 *             'fieldname',
	 *             array( 'translate:[perms.can_delete]' => 'can_delete', 'translate:[perms.can_edit]' => 'can_edit', 'translate:[perms.can_see]' => 'can_see' ),
	 *             array( 'can_see', 'can_delete' ),
	 *          true,
	 *           array( 'class' => 'inputbox', 'size' => 5 )
	 * );
	 *
	 *
	 * example: list with multiple select options and optgroups
	 *
	 * SPHtml_Input::select(
	 *             'fieldname',
	 *             array(
	 *                     'categories' => array( 'translate:[perms.can_delete_categories]' => 'can_delete_categories', 'translate:[perms.can_edit_categories]' => 'can_edit_categories', 'translate:[perms.can_see_categories]' => 'can_see_categories' ),
	 *                     'entries' => array( 'translate:[perms.can_delete_entries]' => 'can_delete_entries', 'translate:[perms.can_edit_entries]' => 'can_edit_entries', 'translate:[perms.can_see_entries]' => 'can_see_entries' ),
	 *             )
	 *             array( 'can_see_categories', 'can_delete_entries', 'can_edit_entries' ),
	 *          true,
	 *           array( 'class' => 'inputbox', 'size' => 5 )
	 * )
	 *
	 * @param string $name - name of the html field
	 * @param array $values - two-dimensional array with values and their labels. array( 'enabled' => 1, 'disabled' => 0 )
	 * @param array $selected - one-dimensional array with selected values
	 * @param bool $multi - multiple select is allowed or not
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string $title - language section for the title tags. If given, the options/optgroup will get a title tag. The title will be search in the language file under the given section
	 */
	public static function select( $name, $values, $selected = null, $multi = false, $params = null, $title = false )
	{
		$params = self::params( $params );
		self::checkArray( $values );
		if ( $selected !== null && !( is_array( $selected ) ) ) {
			$selected = array( ( string )$selected );
		}
		elseif ( !( is_array( $selected ) ) ) {
			$selected = array();
		}
		$cells = array();
		$t = null;
		$gt = null;
		if ( is_array( $values ) && count( $values ) ) {
			foreach ( $values as $v => $l ) {
				/* if one of both values was an array - it is a group */
				if ( ( is_array( $l ) || is_array( $v ) ) && !( isset( $l[ 'label' ] ) ) ) {
					$cells[ ] = "<optgroup label=\"{$v}\"{$gt}>";
					if ( count( $l ) ) {
						foreach ( $l as $ov => $ol ) {
							/** when there is a group */
							if ( is_array( $ol ) && !( isset( $ol[ 'label' ] ) ) ) {
								self::optGrp( $cells, $selected, $ol, $ov );
							}
							else {
								/** when we have special params */
								if ( is_array( $ol ) && ( isset( $ol[ 'label' ] ) ) ) {
									$sel = in_array( ( string )$ol[ 'value' ], $selected, true ) ? ' selected="selected" ' : null;
									$ol = self::cleanOpt( $ol[ 'label' ] );
									$ov = self::cleanOpt( $ol[ 'value' ] );
									$p = null;
									$oParams = array();
									if ( isset( $ol[ 'params' ] ) && count( $ol[ 'params' ] ) ) {
										foreach ( $ol[ 'params' ] as $param => $value ) {
											$oParams[ ] = "{$param}=\"{$value}\"";
										}
									}
									if ( count( $oParams ) ) {
										$p = implode( ' ', $oParams );
										$p = " {$p} ";
									}
									$cells[ ] = "\t<option {$p}{$sel}value=\"{$ov}\"{$t}>{$ol}</option>";
								}
								else {
									$sel = in_array( ( string )$ov, $selected, true ) ? ' selected="selected" ' : null;
									$ol = self::cleanOpt( $ol );
									$ov = self::cleanOpt( $ov );
									$cells[ ] = "\t<option {$sel}value=\"{$ov}\"{$t}>{$ol}</option>";
								}
							}
						}
					}
					$cells[ ] = "</optgroup>";
				}
				else {
					/** when we have special params */
					if ( is_array( $l ) && ( isset( $l[ 'label' ] ) ) ) {
						$sel = in_array( ( string )$l[ 'value' ], $selected, true ) ? ' selected="selected" ' : null;
						$ol = self::cleanOpt( $l[ 'label' ] );
						$ov = self::cleanOpt( $l[ 'value' ] );
						$p = null;
						$oParams = array();
						if ( isset( $l[ 'params' ] ) && count( $l[ 'params' ] ) ) {
							foreach ( $l[ 'params' ] as $param => $value ) {
								$oParams[ ] = "{$param}=\"{$value}\"";
							}
						}
						if ( count( $oParams ) ) {
							$p = implode( ' ', $oParams );
							$p = " {$p} ";
						}
						$cells[ ] = "\t<option {$p}{$sel}value=\"{$ov}\"{$t}>{$ol}</option>";
					}
					else {
						$sel = in_array( ( string )$v, $selected, true ) ? ' selected="selected" ' : null;
						$v = self::cleanOpt( $v );
						$l = self::cleanOpt( self::translate( $l ) );
						$cells[ ] = "<option {$sel}value=\"{$v}\"{$t}>{$l}</option>";

					}
				}
			}
		}
		if ( $multi ) {
			$multi = ' multiple="multiple" ';
			$name .= '[]';
		}
		$cells = implode( "\n\t", $cells );
		$f = "\n<select name=\"{$name}\"{$multi}{$params}>\n\t{$cells}\n</select>\n";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- SelectList '{$name}' Output -->{$f}<!-- SelectList '{$name}' End -->\n\n";
	}

	private static function cleanOpt( $opt )
	{
		return preg_replace( '/(&)([^a-zA-Z0-9#]+)/', '&amp;\2', self::translate( $opt ) );
	}

	private function optGrp( &$cells, $selected, $grp, $title )
	{
		$cells[ ] = "\n\t<optgroup label=\"{$title}\">";
		foreach ( $grp as $v => $l ) {
			$v = SPLang::entities( $v, true );
			if ( is_array( $l ) ) {
				self::optGrp( $cells, $selected, $l, /*Sobi::Txt*/
					( $v ) );
			}
			else {
				$sel = in_array( ( string )$v, $selected, true ) ? ' selected="selected" ' : null;
				$l = SPLang::entities( self::translate( $l ), true );
				$cells[ ] = "\t<option {$sel}value=\"{$v}\">{$l}</option>";
			}
		}
		$cells[ ] = "</optgroup>\n\t";
	}

	/**
	 * Special function to create enabled/disabled states radio list
	 *
	 * @param string $name - name of the html field
	 * @param array $value - selected value
	 * @param string $id - id prefix of the field
	 * @param string $label - label prefix to display beside the fields
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @return string
	 */
	public static function states( $name, $value, $id, $prefix /*, $params = null*/ )
	{
		$value = (int)$value;
		$field = null;
		$field .= "\n" . '<div class="btn-group buttons-radio" data-toggle="buttons-radio" id="' . $id . '">';
		$field .= "\n\t" . '<button type="button" name="' . $name . '" class="btn btn-success' . ( $value ? ' active selected' : '' ) . '" value="1">' . Sobi::Txt( $prefix . '_yes' ) . '</button>';
		$field .= "\n\t" . '<button type="button" name="' . $name . '" class="btn btn-danger' . ( $value ? '' : ' active selected' ) . '" value="0">' . Sobi::Txt( $prefix . '_no' ) . '</button>';
		$field .= "\n" . '</div>';
		return "\n<!-- States '{$name}' Output -->{$field}\n<!-- States '{$name}' End -->\n";
	}

	/**
	 * Creates field with date selector
	 *
	 * @param string $name - name of the html field
	 * @param array $value - selected value
	 * @param string $id - id prefix of the field
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @return string
	 */
	public static function calendar( $name, $value, $id = null, $params = null )
	{
		self::loadCalendar();
		self::checkArray( $params );
		$value = $value ? SPFactory::config()->date( $value, 'calendar.date_format' ) : null;
		$id = $id ? $id : $name;
		$params = array_merge( $params, array( 'id' => $id ) );
		$calendar = self::text( $name, $value, $params );
		$bt = self::translate( SPFactory::config()->key( 'calendar.button_label', ' ... ' ) );
		$bt = "<input name=\"reset\" type=\"reset\" id=\"{$id}CalBt\" class=\"button\" onclick=\"return SPCalendar( '{$id}', '{$id}CalBt');\" value=\"{$bt}\" />";
		$site = SPFactory::config()->key( 'calendar.button_side', 'right' );
		$calendar = ( $site == 'right' ) ? $calendar . $bt : $bt . $calendar;
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$calendar ) );
		return $calendar;
	}

	/**
	 * @return bool
	 */
	private static function loadCalendar()
	{
		static $loaded = false;
		if ( $loaded ) {
			return $loaded;
		}
		$config =& SPFactory::config();
		$header =& SPFactory::header();
		$config->addIniFile( 'etc.calendar' );
		$theme = $config->key( 'calendar.theme', 'system' );
		$dateFormat = $config->key( 'calendar.date_format', 'dd-mm-y' );
		$dateFormatTxt = $config->key( 'calendar.date_format_txt', 'D, M d' );
		$sLang = Sobi::Lang( false );
		$lang = $config->key( 'calendar_lang_map.' . $sLang, 'en' );
		$header->addCssFile( "calendar.calendar-{$theme}" );
		$header->addJsFile( 'calendar.calendar' );
		$header->addJsFile( "calendar.lang.calendar-{$lang}" );
		$header->addJsVarFile( 'calendar.init', md5( "{$dateFormat}_{$dateFormatTxt}" ), array( 'FORMAT' => $dateFormat, 'FORMAT_TXT' => $dateFormatTxt ) );
	}

	/**
	 * @param $name - field name
	 * @param $value - field value
	 * @param string $dateFormat - date format in PHP
	 * @param null $params - additional parameters
	 * @param string $icon - field icon
	 * @return string
	 */
	public static function datePicker( $name, $value, $dateFormat = 'Y-m-d H:i:s', $params = null, $icon = 'th' )
	{
		self::createLangFile();
		$value = strtotime( $value );
		/** The stupid JavaScript to PHP conversion. */
		$jsDateFormat = str_replace(
			array( 'y', 'Y', 'F', 'n', 'm', 'd', 'j', 'h', 'H', 'i', 's' ),
			array( 'yy', 'yyyy', 'MM', 'm', 'mm', 'dd', 'd', 'hh', 'hh', 'ii', 'ss' ),
			$dateFormat
		);
		$valueDisplay = $value ? SPFactory::config()->date( $value, null, $dateFormat ) : null;
		$params = self::checkArray( $params );
		if ( !( isset( $params[ 'id' ] ) ) ) {
			$params[ 'id' ] = SPLang::nid( $name );
		}
		SPFactory::header()
				->addCssFile( 'bootstrap.datepicker' )
				->addJsFile( array( 'locale.' . Sobi::Lang( false ) . '_date_picker', 'bootstrap.datepicker' ) );
		$params = self::params( $params );
		$f = "\n";
		$f .= '<div class="input-append date spDatePicker" data-date-format="' . $jsDateFormat . '">';
		$f .= "\n\t";
		$f .= '<input type="text" value="' . $valueDisplay . '" ' . $params . ' name="' . $name . 'Holder"/>';
		$f .= '<input type="hidden" value="' . $value . '" name="' . $name . '"/>';
		$f .= "\n\t";
		$f .= '<span class="add-on"><i class="icon-' . $icon . '"></i></span>';
		$f .= "\n";
		$f .= '</div>';
		$f .= "\n";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- Date Picker '{$name}' Output -->{$f}<!-- Date Picker '{$name}' End -->\n\n";
	}

	/**
	 * @param $name - field name
	 * @param $value - field value
	 * @param string $dateFormat - date format in PHP
	 * @param null $params - additional parameters
	 * @param string $icon - field icon
	 * @return string
	 */
	public static function dateGetter( $name, $value, $class = null, $dateFormat = 'Y-m-d H:i:s', $params = null )
	{
		self::createLangFile();
		$value = strtotime( $value );
		$valueDisplay = $value ? SPFactory::config()->date( $value, null, $dateFormat ) : null;
		$params = self::checkArray( $params );
		if ( !( isset( $params[ 'id' ] ) ) ) {
			$params[ 'id' ] = SPLang::nid( $name );
		}
		if ( $class ) {
			$params[ 'class' ] = $class;
		}
		$params = self::params( $params );

		$f = "\n";
		$f .= '<div class="spOutput">';
		$f .= "\n\t";
		$f .= '<span ' . $params . '>' . $valueDisplay . '</span>';
		$f .= "\n";
		$f .= '</div>';
		$f .= "\n";

		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- Date Getter '{$name}' Output -->{$f}<!-- Date Getter '{$name}' End -->\n\n";
	}

	private static function createLangFile()
	{
		static $loaded = false;
		if ( !( $loaded ) ) {
			$lang = array(
				'months' => Sobi::Txt( 'JS_CALENDAR_MONTHS' ),
				'monthsShort' => Sobi::Txt( 'JS_CALENDAR_MONTHS_SHORT' ),
				'days' => Sobi::Txt( 'JS_CALENDAR_DAYS' ),
				'daysShort' => Sobi::Txt( 'JS_CALENDAR_DAYS_SHORT' ),
				'daysMin' => Sobi::Txt( 'JS_CALENDAR_DAYS_MINI' ),
				'today' => Sobi::Txt( 'JS_CALENDAR_TODAY' ),
			);
			$check = md5( serialize( $lang ) );
			if ( !( SPLoader::JsFile( 'locale.' . Sobi::Lang( false ) . '_date_picker', false, true, false ) ) || !( stripos( SPFs::read( SPLoader::JsFile( 'locale.' . Sobi::Lang( false ) . '_date_picker', false, false, false ) ), $check ) ) ) {
				foreach ( $lang as $k => $v ) {
					$lang[ $k ] = explode( ',', $v );
				}
				$lang = json_encode( $lang );
				$c = "\nvar spDatePickerLang={$lang}";
				$c .= "\n//{$check}";
				SPFs::write( SPLoader::JsFile( 'locale.' . Sobi::Lang( false ) . '_date_picker', false, false, false ), $c );
			}
		}
		$loaded = true;
	}

	public static function userSelector( $name, $value, $groups = null, $params = null, $icon = 'user', $header = 'USER_SELECT_HEADER', $format = '%user', $orderBy = 'id' )
	{
		static $count = 0;
		static $session = null;
		if ( !( $session ) ) {
			$session = SPFactory::user()->getUserState( 'userSelector', null, array() );
		}
		$params = self::checkArray( $params );
		if ( !( isset( $params[ 'id' ] ) ) ) {
			$params[ 'id' ] = SPLang::nid( $name );
		}
		$user = null;
		SPFactory::header()->addJsFile( 'user_selector' );
		$user = SPUser::getBaseData( ( int )$value );
		$settings = array(
			'groups' => $groups,
			'format' => $format,
			'user' => Sobi::My( 'id' ),
			'ordering' => $orderBy,
			'time' => microtime( true ),
		);
		if ( count( $session ) ) {
			foreach ( $session as $id => $data ) {
				if ( microtime( true ) - $data[ 'time' ] > 3600 ) {
					unset( $session[ $id ] );
				}
			}
		}
		$ssid = md5( microtime() . Sobi::My( 'id' ) . ++$count );
		$session[ $ssid ] =& $settings;
		SPFactory::user()->setUserState( 'userSelector', $session );
		$userData = null;
		if ( $user ) {
			$replacements = array();
			preg_match_all( '/\%[a-z]*/', $format, $replacements );
			$placeholders = array();
			if ( isset( $replacements[ 0 ] ) && count( $replacements[ 0 ] ) ) {
				foreach ( $replacements[ 0 ] as $placeholder ) {
					$placeholders[ ] = str_replace( '%', null, $placeholder );
				}
			}
			if ( count( $replacements ) ) {
				foreach ( $placeholders as $attribute ) {
					if ( isset( $user->$attribute ) ) {
						$format = str_replace( '%' . $attribute, $user->$attribute, $format );
					}
				}
				$userData = $format;
			}
		}
		$modal = '<div class="response btn-group" data-toggle="buttons-radio"></div><br/><button class="btn btn-block hide more" type="button">' . Sobi::Txt( 'LOAD_MORE' ) . '</button>';
		$filter = '<input type="text" placeholder="' . Sobi::Txt( 'FILTER' ) . '" class="search pull-right" name="q">';
		$id = $params[ 'id' ];
		$params = self::params( $params );
		$f = "\n";
		$f .= '<div class="spUserSelector">';
		$f .= '<div class="input-append">';
		$f .= "\n\t";
		$f .= '<input type="text" value="' . $userData . '" ' . $params . ' name="' . $name . 'Holder" readonly="readonly" class="trigger user-name"/>';
		$f .= '<span class="add-on trigger"><i class="icon-' . $icon . '"></i></span>';
		$f .= '</div>';
		$f .= '<input type="hidden" value="' . $value . '" name="' . $name . '" rel="selected"/>';
		$f .= '<input type="hidden" value="' . $ssid . '" name="' . $name . 'Ssid"/>';
		$f .= '<input type="hidden" value="1" name="' . SPFactory::mainframe()->token() . '"/>';
		$f .= "\n\t";
		$f .= "\n";
		$f .= self::modalWindow( Sobi::Txt( $header ) . $filter, $id . '-window', $modal );
		$f .= '</div>';
		$f .= "\n";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- User Picker '{$name}' Output -->{$f}<!-- User Picker '{$name}' End -->\n\n";
	}

	public static function userGetter( $name, $value, $params = null, $class = null, $format = '%user' )
	{
		$params = self::checkArray( $params );
		if ( !( isset( $params[ 'id' ] ) ) ) {
			$params[ 'id' ] = SPLang::nid( $name );
		}
		if ( $class ) {
			$params[ 'class' ] = $class;
		}
		$user = null;
		$user = SPUser::getBaseData( ( int )$value );
		$userData = null;
		if ( $user ) {
			$replacements = array();
			preg_match_all( '/\%[a-z]*/', $format, $replacements );
			$placeholders = array();
			if ( isset( $replacements[ 0 ] ) && count( $replacements[ 0 ] ) ) {
				foreach ( $replacements[ 0 ] as $placeholder ) {
					$placeholders[ ] = str_replace( '%', null, $placeholder );
				}
			}
			if ( count( $replacements ) ) {
				foreach ( $placeholders as $attribute ) {
					if ( isset( $user->$attribute ) ) {
						$format = str_replace( '%' . $attribute, $user->$attribute, $format );
					}
				}
				$userData = $format;
			}
		}
		$params = self::params( $params );
		$f = "\n";
		$f .= '<div class="spOutput">';
		$f .= "\n\t";
		$f .= '<span ' . $params . '>' . $userData . '</span>';
		$f .= "\n";
		$f .= '</div>';
		$f .= "\n";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
		return "\n<!-- User Getter '{$name}' Output -->{$f}<!-- User Getter '{$name}' End -->\n\n";
	}


	public static function modalWindow( $header, $id = null, $content = null, $classes = 'modal hide', $closeText = 'CLOSE', $saveText = 'SAVE', $style = null )
	{
		$html = null;
		if ( $style ) {
			$style = " style=\"{$style}\"";
		}
		$id = strlen( $id ) ? '" id="' . $id . '"' : null;
		$html .= '<div class="' . $classes . $id . $style . '>
					<div class="modal-header">
						<h3>' . ( $header ) . '</h3>
					</div>
					<div class="modal-body">
					' . $content . '
					</div>
					<div class="modal-footer">
						<a href="#" class="btn" data-dismiss="modal">' . Sobi::Txt( $closeText ) . '</a>
						<a href="#" id="' . $id . 'Save" class="btn btn-primary save" data-dismiss="modal">' . Sobi::Txt( $saveText ) . '</a>
					</div>
				</div>
		';
		return $html;
	}

	public static function hidden( $name, $value = null, $id = null )
	{
		$id = $id ? $id : SPLang::nid( $name );
		$f = "\n<input type=\"hidden\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\"/>";
		return "\n<!--  '{$name}' Output -->{$f}<!-- '{$name}' End -->\n\n";
	}
}
