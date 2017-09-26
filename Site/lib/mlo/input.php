<?php
/**
 * @package: SobiPro Library
 *
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET
 *
 * @copyright Copyright (C) 2006 - 2017 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license   GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author  Radek Suski
 * @version 1.0
 * @since   1.0
 * @created 11-Jan-2009 12:45:28 PM
 */
abstract class SPHtml_Input
{

	public static function __callStatic( $name, $args )
	{
		if ( defined( 'SOBIPRO_ADM' ) ) {
			return call_user_func_array( [ 'self', '_' . $name ], $args );
		}
		else {
			static $className = false;
			if ( !( $className ) ) {
				$package = Sobi::Reg( 'current_template' );
				if ( SPFs::exists( Sobi::FixPath( $package . '/input.php' ) ) ) {
					$path = Sobi::FixPath( $package . '/input.php' );
					ob_start();
					$content = file_get_contents( $path );
					$class = [];
					preg_match( '/\s*(class)\s+(\w+)/', $content, $class );
					if ( isset( $class[ 2 ] ) ) {
						$className = $class[ 2 ];
					}
					else {
						Sobi::Error( 'Custom Input Class', SPLang::e( 'Cannot determine class name in file %s.', str_replace( SOBI_ROOT, null, $path ) ), SPC::WARNING, 0 );

						return false;
					}
					require_once( $path );
				}
				else {
					$className = true;
				}
			}
			if ( is_string( $className ) && method_exists( $className, $name ) ) {
				return call_user_func_array( [ $className, $name ], $args );
			}
			else {
				return call_user_func_array( [ 'self', '_' . $name ], $args );
			}
		}
	}

	/**
	 * @param mixed $params
	 */
	public static function checkArray( &$params )
	{
		if ( $params && is_string( $params ) && strstr( $params, ',' ) ) {
			$class = SPLoader::loadClass( 'types.array' );
			$arr = new $class;
			$arr->fromString( $params, ',', '=' );
			$params = $arr->toArr();
		}
		$p = [];
		if ( is_array( $params ) ) {
			foreach ( $params as $i => $k ) {
				$p[ trim( $i ) ] = ( $k );
			}
		}
		$params = is_array( $p ) ? $p : [];
	}

	/**
	 * @param string $txt
	 *
	 * @return string
	 */
	protected static function _translate( $txt )
	{
		if ( strstr( $txt, 'translate:' ) ) {
			$matches = [];
			preg_match( '/translate\:\[(.*)\]/', $txt, $matches );
			$txt = str_replace( $matches[ 0 ], Sobi::Txt( $matches[ 1 ] ), $txt );
		}

		return Sobi::Clean( $txt );
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public static function params( $params )
	{
		$add = null;
		self::checkArray( $params );
		if ( $params && is_array( $params ) && count( $params ) ) {
			foreach ( $params as $param => $v ) {
				if ( in_array( $param, [ 'required' ] ) ) {
					continue;
				}
				$v = trim( str_replace( '"', '\'', $v ) );
				$param = str_replace( [ '\'', '"' ], null, trim( $param ) );
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
	 *
	 * @return string
	 */
	public static function _file( $name, $size = 50, $params = null, $accept = '*' )
	{
		$params = self::params( $params );
		$f = "<input name=\"{$name}\" type=\"file\" size=\"{$size}\" value=\"\" accept=\"{$accept}\"{$params}/>";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- FileBox '{$name}' Output -->{$f}<!-- FileBox '{$name}' End -->\n\n";
		return "{$f}\n";
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
	 *
	 * @return string
	 */
	public static function _fileUpload( $name, $accept = '*', $value = null, $class = 'spFileUpload', $task = 'file.upload', $scripts = [ 'jquery', 'jquery-form', 'fileupload' ], $request = null )
	{
		if ( is_string( $scripts ) ) {
			$scripts = SPFactory::config()->structuralData( $scripts );
		}
		SPFactory::header()->addJsFile( $scripts );
		if ( !( $request ) ) {
			$request = [
					'option' => 'com_sobipro',
					'task' => $task,
					'sid' => Sobi::Section(),
					'ident' => $name . '-file',
					SPFactory::mainframe()->token() => 1,
					'format' => 'raw'
			];
		}
		$classes = [ 'class' => 'hide spFileUploadHidden' ];
		SPLoader::loadClass( 'env.browser' );
		$browser = SPBrowser::getInstance()->get( 'browser' );
		$stupidInternetExplorer = false;
		if ( strstr( strtolower( $browser ), 'internet explorer' ) ) {
			$classes = [ 'class' => '' ];
			$stupidInternetExplorer = true;
		}
		$f = null;
		$f .= "<div class=\"{$class} spUpload\" data-section=" . Sobi::Section() . ">";
		$f .= '<div class="file">';
		$f .= self::file( $name . '-file', 0, $classes, $accept );
		$f .= '</div>';
		$b3class = '';
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$b3class = ' form-control';
		}
		if ( !( $stupidInternetExplorer ) ) {
			$f .= "<input type=\"text\" readonly=\"readonly\" class=\"input-large selected pull-left{$b3class}\" value=\"{$value}\"/>";
		}
		$f .= '<div class="btn-group">';
		if ( !( $stupidInternetExplorer ) ) {
			$f .= '<button class="btn btn-default select" type="button"><i class="' . Sobi::Ico( 'upload-field.search-button' ) . '"></i>&nbsp;' . Sobi::Txt( 'UPLOAD_SELECT' ) . '</button>';
		}
		$f .= '<button class="btn btn-default upload hide" disabled="disabled" type="button" rel=\'' . json_encode( $request ) . '\'>' . Sobi::Txt( 'START_UPLOAD' ) . '&nbsp;<i class="icon-upload-alt"></i></button>';
		$f .= '<button class="btn btn-default remove" disabled="disabled" type="button">' . '&nbsp;<i class="' . Sobi::Ico( 'upload-field.remove-button' ) . '"></i></button>';
		$f .= '</div>';

		$f .= '<div class="hide progress-container">';
		$f .= '<div class="progress progress-success">';
		$f .= '<div class="bar progress-bar progress-bar-success"><span class="progress-message">0%</span></div>';
		$f .= '</div>';
		$f .= '</div>';
		//no close button as it won't open again without reload -> no further messages
//		$f .= '<div class="alert hide"><button type="button" class="close" data-dismiss="alert">×</button><div>&nbsp;</div></div>';
		$f .= '<div class="alert hide"><div>&nbsp;</div></div>';
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
	 *
	 * @return string
	 */
	public static function _text( $name, $value = null, $params = null )
	{
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			SPHtml_Input::checkArray( $params );
			if ( isset( $params[ 'class' ] ) ) {
				$params[ 'class' ] .= ' form-control';
			}
			else {
				$params[ 'class' ] = ' form-control';
			}
		}
		$params = self::params( $params );
		$value = strlen( $value ) ? str_replace( '"', '&quot;', SPLang::entities( $value, true ) ) : null;
		$f = "<input type=\"text\" name=\"{$name}\" value=\"{$value}\"{$params}/>";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

		//"\n<!-- InputBox '{$name}' Output -->{$f}<!-- InputBox '{$name}' End -->\n\n";
		return "{$f}\n";
	}

	/**
	 * Creates simple HTML SubmitButton
	 *
	 * @param string $name - name of the html field
	 * @param string $value - selected value
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 *
	 * @return string
	 */
	public static function _submit( $name, $value = null, $params = null )
	{
		$params = self::params( $params );
		$value = self::translate( $value );
		$value = strlen( $value ) ? SPLang::entities( /*Sobi::Txt*/
				( $value ), true ) : null;
		$f = "<input type=\"submit\" name=\"{$name}\" value=\"{$value}\"{$params}/>";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- SubmitButton '{$name}' Output -->{$f}<!-- SubmitButton '{$name}' End -->\n\n";
		return "{$f}\n";
	}

	/**
	 * Creates a HTML file box
	 *
	 * @param $width
	 * @return string
	 * @internal param string $name - name of the html field
	 * @internal param string $value - selected value
	 * @internal param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 *
	 */
	public static function _translateWidth( $width )
	{

		switch ( $width ) {
			case 1:
				$newwidth = 'input-small';
				break;
			case 2:
			case 3:
				$newwidth = 'input-medium';
				break;
			case 4:
				$newwidth = 'input-large';
				break;
			case 5:
			case 6:
				$newwidth = 'input-xlarge';
				break;
			case 7:
			case 8:
				$newwidth = 'input-splarge';
				break;
			case 9:
			case 10:
			default:
				$newwidth = 'input-xxxlarge'; //no limit
				break;
		}

		return $newwidth;
	}

	/**
	 * Displays a hidden token field
	 * @return    string
	 */
	public static function _token()
	{
		return '<input type="hidden" name="' . SPFactory::mainframe()->token() . '" value="1" />';
	}

	/**
	 * Creates simple HTML SubmitButton
	 *
	 * @param string $name - name of the html field
	 * @param string $value - selected value
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string $class
	 * @param null $icon
	 *
	 * @return string
	 */
	public static function _button( $name, $value = null, $params = null, $class = null, $icon = null )
	{
		self::checkArray( $params );
		$f = null;
		$h = null;
		// bootstrap modal needs an href
		if ( isset( $params[ 'href' ] ) && !( strstr( $params[ 'href' ], '#' ) ) ) {
			SPFactory::header()->addJsCode( "
				function _{$name}Redirect()
				{
					window.location ='{$params['href']}';
					return false;
				}
			" );
			$params[ 'href' ] = htmlentities( $params[ 'href' ] );
			// sending data twice in payment screen
//			$a = "<a href=\"{$params[ 'href' ]}\" class=\"spDisabled\">";
//			$a = "<a href=\"#\" class=\"spDisabled\">";
			$h = " onclick=\"{$name}Redirect()\"";
			unset( $params[ 'href' ] );
		}
		if ( $class ) {
			$params[ 'class' ] = $class;
		}
		$params = self::params( $params );
		$value = self::translate( $value );
		if ( $icon ) {
			$value = "<i class=\"icon-{$icon}\"></i> " . $value;
		}
		$f = "<button type=\"button\" name=\"{$name}\" {$h}{$params}>{$value}</button>";
		if ( isset( $a ) ) {
			$f = $a . $f . "</a>";
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- Button '{$name}' Output --> {$f}<!-- Button '{$name}' End -->\n\n";
		return "{$f}\n";
	}

	/**
	 * Creates a textarea field with or without WYSIWYG editor
	 *
	 * @param string $name - name of the html field
	 * @param string $value - selected value
	 * @param bool $editor - enables WYSIWYG editor
	 * @param int|string $width - width of the created textarea field in pixel
	 * @param int $height - height of the created textarea field in pixel
	 * @param array|string $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string | array $editorParams
	 * @return string
	 */
	public static function _textarea( $name, $value = null, $editor = false, $width = '', $height = 350, $params = '', $editorParams = null )
	{
		if ( !( is_array( $editorParams ) ) && strlen( $editorParams ) ) {
			$editorParams = SPFactory::config()->structuralData( $editorParams );
		}
		self::checkArray( $params );
		if ( !isset( $params[ 'style' ] ) ) {
			$params[ 'style' ] = "height: {$height}px;";
		}
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$editorParams[ 'class' ] = "form-control";
		}

		Sobi::Trigger( 'BeforeCreateField', ucfirst( __FUNCTION__ ), [ &$name, &$value, &$editor, &$width, &$height, &$params ] );
		$value = SPLang::entities( $value );
		if ( $editor ) {
			$e = Sobi::Cfg( 'html.editor', 'cms.html.editor' );
			$c = SPLoader::loadClass( $e );
			if ( $c ) {
				$e = new $c();
				$area = $e->display( $name, $value, $width, $height, ( boolean )Sobi::Cfg( 'html.editor_buttons', false ), $editorParams );
			}
		}
		else {
			if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
				SPHtml_Input::checkArray( $params );
				if ( isset( $params[ 'class' ] ) ) {
					$params[ 'class' ] .= ' form-control';
				}
				else {
					$params[ 'class' ] = ' form-control';
				}
			}
			$params = self::params( $params );
			$area = "<textarea name=\"{$name}\" {$params}>{$value}</textarea>";
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$area ] );

//		return "\n<!-- TextArea '{$name}' Output -->\n{$area}\n<!-- TextArea '{$name}' End -->\n\n";
		return "{$area}\n";
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
	 *
	 * @return string
	 */
	public static function _radio( $name, $value, $label = null, $id = null, $checked = false, $params = null, $order = [ 'field', 'image', 'label' ], $image = null )
	{
		$params = self::params( $params );
		if ( !( is_bool( $checked ) ) ) {
			$checked = ( ( string )$checked == ( string )$value ) ? true : false;
		}
		$label = strlen( $label ) ? self::translate( $label ) : null;
		$checked = $checked ? " checked=\"checked\" " : null;
		$$name = self::cleanOpt( $name );
		$value = self::cleanOpt( $value );
		$f = "<input type=\"radio\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\"{$checked}{$params}/>";

		$l = strlen( $label ) ? "\n<label for=\"{$id}\">{$label}</label>" : null;
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$lstart = strlen( $label ) ? "\n<label for=\"{$id}\" class=\"radio-inline\">" : null;
		}
		else {
			$lstart = strlen( $label ) ? "\n<label for=\"{$id}\" class=\"radio inline\">" : null;
		}

		$lend = strlen( $label ) ? "</label>" : null;
		$lcontent = strlen( $label ) ? $label : null;

		if ( $image ) {
			$image = "\n<img src=\"{$image}\" alt=\"{$label}\"/>";
		}
		if ( is_array( $order ) ) {
//			if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			//im Moment weiss ich nichts besseres hierfür
			$f = ( $order == 'left' ) ? $lstart . $lcontent . $f . $lend : $lstart . $f . $lcontent . $lend;
//			}
//			else {
//				$field = null;
//				foreach ( $order as $position ) {
//					switch ( $position ) {
//						case 'field':
//							$field .= $f;
//							break;
//						case 'label':
//							$field .= $l;
//							break;
//						case 'image':
//							$field .= $image;
//							break;
//					}
//				}
//				$f = $field;
//			}
		}
		else {
//			if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$f = ( $order == 'left' ) ? $lstart . $lcontent . $f . $lend : $lstart . $f . $lcontent . $lend;
//			}
//			else {
//				$f = ( $order == 'left' ) ? $l . $f : $f . $l;
//			}
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- RadioButton '{$name}' Output -->{$f}\n<!-- RadioButton '{$name}' End -->\n";
		return "\n{$f}\n\n";
	}

	/**
	 * Creates group of check boxes
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
	 * @param array $order
	 * @param bool $asArray - returns array instead of a string
	 *
	 * @internal param string $field - on which site from the label the field should be displayed
	 * @return string
	 */
	public static function _checkBoxGroup( $name, $values, $id, $selected = null, $params = null, $order = [ 'field', 'image', 'label' ], $asArray = false )
	{
		self::checkArray( $values );
		if ( $selected !== null && !( is_array( $selected ) ) ) {
			$selected = [ ( string )$selected ];
		}
		elseif ( !( is_array( $selected ) ) ) {
			$selected = [];
		}
		$list = [];
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
//				if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
//					$container = '<div class="checkbox-inline ' . $order . '">';
//				$container = '<div class="checkbox-inline">';
//					$containerend = '</div>';
//				}
//				else {
//					$container = '<span>';
//					$containerend = '</span>';
//				}
//				$list[ ] = $container . self::checkbox( $name . '[]', $value, $label, $id . '_' . $value, $checked, $params, $order, $image ) . $containerend;
				$list[] = self::checkbox( $name . '[]', $value, $label, $id . '_' . $value, $checked, $params, $order, $image );
			}
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$list ] );

		return $asArray ? $list : ( count( $list ) ? implode( "\n", $list ) : null );
	}

	/**
	 * Creates single checkbox
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
	 * @param null $image
	 *
	 * @return string
	 */
	public static function _checkbox( $name, $value, $label = null, $id = null, $checked = false, $params = null, $order = [ 'field', 'image', 'label' ], $image = null )
	{
		$params = self::params( $params );
		if ( !( is_bool( $checked ) ) ) {
			$checked = ( ( string )$checked == ( string )$value ) ? true : false;
		}
		$label = (string)$label;
		$label = strlen( $label ) ? self::cleanOpt( self::translate( $label ) ) : null;
		$checked = $checked ? " checked=\"checked\" " : null;
		$ids = $id ? "id=\"{$id}\" " : $id;
		$$name = self::cleanOpt( $name );
		$value = self::cleanOpt( $value );
		$f = "<input type=\"checkbox\" name=\"{$name}\" {$ids}value=\"{$value}\"{$checked}{$params}/>";

		$l = strlen( $label ) ? "\n<label for=\"{$id}\">{$label}</label>" : null;
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$lstart = $label ? "\n<label for=\"{$id}\" class=\"checkbox-inline\">" : null;
		}
		else {
			if ( defined( 'SOBIPRO_ADM' ) ) {
				$lstart = $label ? "\n<label for=\"{$id}\" class=\"checkbox\">" : null;
			}
			else {
				$lstart = $label ? "\n<label for=\"{$id}\" class=\"checkbox inline\">" : null;
			}
		}

		$lend = $label ? "</label>" : null;
		$lcontent = $label ? $label : null;

		if ( $image ) {
			$image = "\n<img src=\"{$image}\" alt=\"{$label}\"/>";
		}
//		if ( is_array( $order ) ) {
		//if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
		//im Moment weiss ich nichts besseres hierfür
//				$f = ( $order == 'left' ) ? $lstart . $lcontent . $f . $lend : $lstart . $f . $lcontent . $lend;
//			}
//			else {
//				$field = null;
//				foreach ( $order as $position ) {
//					switch ( $position ) {
//						case 'field':
//							$field .= $f;
//							break;
//						case 'label':
//							//does not comply with BS3 style
//							$field .= $l;
//							break;
//						case 'image':
//							$field .= $image;
//							break;
//					}
//				}
//				$f = $field;
//			}
//		}
//		else {
//			if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
//				$f = ( $order == 'left' ) ? $lstart . $lcontent . $f . $lend : $lstart . $f . $lcontent . $lend;
		$f = $lstart . $f . $lcontent . $lend;
//			}
//			else {
//				$f = ( $order == 'left' ) ? $l . $f : $f . $l;
//			}
//		}
//		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), array( &$f ) );
////		return "\n<!-- CheckBox '{$name}' Output -->{$f}\n<!-- CheckBox '{$name}' End -->\n";
		return "{$f}\n";
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
	 *
	 * @return string
	 */
	public static function _radioList( $name, $values, $id, $checked = null, $params = null, $field = 'left', $asArray = false )
	{
		self::checkArray( $values );

//		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
//			$container = '<div class="radio-inline ' . $field . '">';
//			$containerend = '</div>';
//		}
//		else {
//			$container = '<span>';
//			$containerend = '</span>';
//		}

		$list = [];
		if ( count( $values ) ) {
			foreach ( $values as $value => $label ) {
				if ( is_numeric( $value ) ) {
					$Id = $id . '_' . ( $value == 1 ? 'yes' : ( $value == 0 ? 'no' : $value ) );
				}
				else {
					$Id = $id . '_' . $value;
				}
//				$list[ ] = $container . self::radio( $name, $value, $label, $Id, $checked, $params, $field ) . $containerend;
				$list[] = self::radio( $name, $value, $label, $Id, $checked, $params, $field );
			}
		}
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$list ] );

		return $asArray ? $list : ( count( $list ) ? implode( "\n", $list ) : null );
	}


	public static function createOptions( $values, $selected = null )
	{
		$cells = [];
		$t = null;
		$gt = null;
		if ( is_array( $values ) && count( $values ) ) {
			foreach ( $values as $v => $l ) {
				/* if one of both values was an array - it is a group */
				if ( ( is_array( $l ) || is_array( $v ) ) && !( isset( $l[ 'label' ] ) ) ) {
					$cells[] = "<optgroup label=\"{$v}\"{$gt}>";
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
									$oParams = [];
									if ( isset( $ol[ 'params' ] ) && count( $ol[ 'params' ] ) ) {
										foreach ( $ol[ 'params' ] as $param => $value ) {
											$oParams[] = "{$param}=\"{$value}\"";
										}
									}
									if ( count( $oParams ) ) {
										$p = implode( ' ', $oParams );
										$p = " {$p} ";
									}
									$cells[] = "\t<option {$p}{$sel}value=\"{$ov}\"{$t}>{$ol}</option>";
								}
								else {
									$sel = in_array( ( string )$ov, $selected, true ) ? ' selected="selected" ' : null;
									$ol = self::cleanOpt( $ol );
									$ov = self::cleanOpt( $ov );
									$cells[] = "\t<option {$sel}value=\"{$ov}\"{$t}>{$ol}</option>";
								}
							}
						}
					}
					$cells[] = "</optgroup>";
				}
				else {
					/** when we have special params */
					if ( is_array( $l ) && ( isset( $l[ 'label' ] ) ) ) {
						$sel = in_array( ( string )$l[ 'value' ], $selected, true ) ? ' selected="selected" ' : null;
						$ol = self::cleanOpt( $l[ 'label' ] );
						$ov = self::cleanOpt( $l[ 'value' ] );
						$p = null;
						$oParams = [];
						if ( isset( $l[ 'params' ] ) && count( $l[ 'params' ] ) ) {
							foreach ( $l[ 'params' ] as $param => $value ) {
								$oParams[] = "{$param}=\"{$value}\"";
							}
						}
						if ( count( $oParams ) ) {
							$p = implode( ' ', $oParams );
							$p = " {$p} ";
						}
						$cells[] = "\t<option {$p}{$sel}value=\"{$ov}\"{$t}>{$ol}</option>";
					}
					else {
						$sel = in_array( ( string )$v, $selected, true ) ? ' selected="selected" ' : null;
						$v = self::cleanOpt( $v );
						$l = self::cleanOpt( self::translate( $l ) );
						$cells[] = "<option {$sel}value=\"{$v}\"{$t}>{$l}</option>";

					}
				}
			}
		}

		return $cells;
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
	 *
	 * @return string
	 * @internal param string $title - language section for the title tags. If given, the options/optgroup will get a title tag. The title will be search in the language file under the given section
	 */
	public static function _select( $name, $values, $selected = null, $multi = false, $params = null )
	{
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			SPHtml_Input::checkArray( $params );
			if ( isset( $params[ 'class' ] ) ) {
				$params[ 'class' ] .= ' form-control';
			}
			else {
				$params[ 'class' ] = ' form-control';
			}
		}
		if ( is_array( $params ) && ( isset( $params[ 'size' ] ) && $params[ 'size' ] == 1 ) ) {
			unset( $params[ 'size' ] );
		}
		$data = self::createDataTag( $params );
		$params = self::params( $params );
		if ( strstr( $name, '_array' ) ) {
			self::checkArray( $selected );
		}
		self::checkArray( $values );
		if ( $selected !== null && !( is_array( $selected ) ) ) {
			$selected = [ ( string )$selected ];
		}
		elseif ( !( is_array( $selected ) ) ) {
			$selected = [];
		}

		$cells = self::createOptions( $values, $selected );

		if ( $multi ) {
			$multi = ' multiple="multiple" ';
			$name .= '[]';
		}
		$cells = implode( "\n\t", $cells );
		$f = "<select name=\"{$name}\"{$multi}{$params}{$data}>\n\t{$cells}\n</select>";
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- SelectList '{$name}' Output -->{$f}<!-- SelectList '{$name}' End -->\n\n";
		return "{$f}\n";
	}

	public static function cleanOpt( $opt )
	{
		return preg_replace( '/(&)([^a-zA-Z0-9#]+)/', '&amp;\2', self::translate( $opt ) );
	}

	protected function optGrp( &$cells, $selected, $grp, $title )
	{
		$cells[] = "\n\t<optgroup label=\"{$title}\">";
		foreach ( $grp as $v => $l ) {
			$v = SPLang::entities( $v, true );
			if ( is_array( $l ) ) {
				self::optGrp( $cells, $selected, $l, /*Sobi::Txt*/
						( $v ) );
			}
			else {
				$sel = in_array( ( string )$v, $selected, true ) ? ' selected="selected" ' : null;
				$l = SPLang::entities( self::translate( $l ), true );
				$cells[] = "\t<option {$sel}value=\"{$v}\">{$l}</option>";
			}
		}
		$cells[] = "</optgroup>\n\t";
	}

	/**
	 * Special function _to create enabled/disabled states radio list
	 *
	 * @param string $name - name of the html field
	 * @param array $value - selected value
	 * @param string $id - id prefix of the field
	 * @param string $label - label prefix to display beside the fields
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @param string $side - on which site from the field the label should be displayed
	 *
	 * @return string
	 * @deprecated
	 */
	public static function _states( $name, $value, $id, $label, $params = null, $side = 'right' )
	{
		return self::radioList( $name, [ '0' => "translate:[{$label}_no]", '1' => "translate:[{$label}_yes]" ], $id, ( int )$value, $params, $side );
	}

	/**
	 * Special function _to create enabled/disabled states radio list
	 *
	 * @param string $name - name of the html field
	 * @param array $value - selected value
	 * @param string $id - id prefix of the field
	 * @param        $prefix
	 *
	 * @internal param string $label - label prefix to display beside the fields
	 * @internal param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 * @return string
	 */
	public static function _toggle( $name, $value, $id, $prefix /*, $params = null*/ )
	{
		$value = (int)$value;
		$field = '<div class="btn-group buttons-radio" data-toggle="buttons-radio" id="' . $id . '">';
		$field .= '<button type="button" name="' . $name . '" class="btn btn-success' . ( $value ? ' active selected' : '' ) . '" value="1">' . Sobi::Txt( $prefix . '_yes' ) . '</button>';
		$field .= '<button type="button" name="' . $name . '" class="btn btn-danger' . ( $value ? '' : ' active selected' ) . '" value="0">' . Sobi::Txt( $prefix . '_no' ) . '</button>';
		$field .= '</div>';

//		return "\n<!-- States '{$name}' Output -->{$field}\n<!-- States '{$name}' End -->\n";
		return "{$field}\n";
	}

	/**
	 * Creates field with date selector
	 *
	 * @param string $name - name of the html field
	 * @param array $value - selected value
	 * @param string $id - id prefix of the field
	 * @param array $params - two-dimensional array with additional html parameters. Can be also string defined, comma separated array with equal sign as key to index separator.
	 *
	 * @return string
	 */
	public static function _calendar( $name, $value, $id = null, $params = null )
	{
//		self::loadCalendar();
//		self::checkArray( $params );
//		$value = $value ? SPFactory::config()->date( $value, 'calendar.date_format' ) : null;
//		$id = $id ? $id : $name;
//		$params = array_merge( $params, [ 'id' => $id ] );
//		$calendar = self::text( $name, $value, $params );
//		$bt = self::translate( SPFactory::config()->key( 'calendar.button_label', ' ... ' ) );
//		$bt = "<input name=\"reset\" type=\"reset\" id=\"{$id}CalBt\" class=\"button\" onclick=\"return SPCalendar( '{$id}', '{$id}CalBt');\" value=\"{$bt}\" />";
//		$site = SPFactory::config()->key( 'calendar.button_side', 'right' );
//		$calendar = ( $site == 'right' ) ? $calendar . $bt : $bt . $calendar;
//		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$calendar ] );
//
//		return $calendar;
	}

	/**
	 * @return bool
	 */
	protected static function _loadCalendar()
	{
//		static $loaded = false;
//		if ( $loaded ) {
//			return $loaded;
//		}
//		$config =& SPFactory::config();
//		$header =& SPFactory::header();
//		$config->addIniFile( 'etc.calendar' );
//		$theme = $config->key( 'calendar.theme', 'system' );
//		$dateFormat = $config->key( 'calendar.date_format', 'dd-mm-y' );
//		$dateFormatTxt = $config->key( 'calendar.date_format_txt', 'D, M d' );
//		$sLang = Sobi::Lang( false );
//		$lang = $config->key( 'calendar_lang_map.' . $sLang, 'en' );
//		$header->addCssFile( "calendar.calendar-{$theme}" );
//		$header->addJsFile( 'calendar.calendar' );
//		$header->addJsFile( "calendar.lang.calendar-{$lang}" );
//		$header->addJsVarFile( 'calendar.init', md5( "{$dateFormat}_{$dateFormatTxt}" ), [ 'FORMAT' => $dateFormat, 'FORMAT_TXT' => $dateFormatTxt ] );
	}

	/**
	 * @param        $name - field name
	 * @param        $value - field value
	 * @param string $dateFormat - date format in PHP
	 * @param null $params - additional parameters
	 * @param string $icon - field icon
	 * @param bool $addOffset
	 * @param null $timeOffset
	 *
	 * @return string
	 */
	public static function _datePicker( $name, $value, $dateFormat = 'Y-m-d H:i:s', $params = null, $icon = 'th', $addOffset = false, $timeOffset = null )
	{
		self::createLangFile();
		$timeOffset = strlen( $timeOffset ) ? $timeOffset : Sobi::Cfg( 'time_offset' );
		// another mystery - what the heck was this supposed to do?
//		$value = ( ( int ) $value != 0 && $value ) ? strtotime( $value ) : null;
		/** The stupid JavaScript to PHP conversion. */
//		$jsDateFormat = str_replace(
//			array( 'n', 'm', 'd', 'j', 'h', 'H', 'i', 's', 'A' ),
//			array( 'm', 'MM', 'dd', 'd', 'HH', 'hh', 'mm', 'ss', 'PP' ),
//			$dateFormat
//		);

		if ( strstr( $dateFormat, 'A' ) ) {
			$dateFormat = str_replace( [ 'h', 'H' ], [ 'g', 'G' ], $dateFormat );
		}
		$jsDateFormat = $dateFormat;
		$jsReplacements = [
				'y' => 'yy',
				'Y' => 'yyyy',
				'F' => 'MM',
				'n' => 'm',
				'm' => 'MM',
				'd' => 'dd',
				'j' => 'd',
				'H' => 'hh',
				'g' => 'HH',
				'G' => 'HH',
				'i' => 'mm',
				's' => 'ss',
				'A' => 'PP'
		];
		foreach ( $jsReplacements as $php => $js ) {
			$jsDateFormat = str_replace( $php, $js, $jsDateFormat );
		}
		if ( $value && !( is_numeric( $value ) ) ) {
			$value = $addOffset ? strtotime( $value . 'UTC' ) : strtotime( $value );
		}
		$offset = null;
		if ( $addOffset ) {
			$offset = SPFactory::config()->getTimeOffset();
		}
		$valueDisplay = $value ? SPFactory::config()->date( $value + $offset, null, $dateFormat, $addOffset ) : null;
//		SPConfig::debOut( gmdate( "l jS \of F Y h:i:s A", $value + $offset ) );
//		SPConfig::debOut( $valueDisplay );
		self::checkArray( $params );
		if ( !( isset( $params[ 'id' ] ) ) ) {
			$params[ 'id' ] = SPLang::nid( $name );
		}
		if ( !( isset( $params[ 'data' ] ) ) ) {
			$params[ 'data' ] = [];
		}
		if ( strstr( $dateFormat, 'A' ) ) {
			$params[ 'data' ][ 'am-pm' ] = 'true';
		}
//		$offset = 0;
		$params[ 'data' ][ 'format' ] = $jsDateFormat;
		if ( $addOffset ) {
			$params[ 'data' ][ 'time-zone' ] = $timeOffset;
			$params[ 'data' ][ 'time-offset' ] = SPFactory::config()->getTimeOffset();
//			$offset = $params[ 'data' ][ 'time-offset' ];
		}
		$bs3 = ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) );
		$data = self::createDataTag( $params );
		SPFactory::header()
				->addCssFile( 'bootstrap.datepicker' )
				->addJsFile( [ 'locale.' . Sobi::Lang( false ) . '_date_picker', 'bootstrap.datepicker' ] );
		if ( $bs3 ) {
			$f = '<div class="input-group input-append date spDatePicker">';
			if ( !( isset( $params[ 'class' ] ) ) ) {
				$params[ 'class' ] = null;
			}
			$params[ 'class' ] .= ' form-control';
		}
		else {
			$f = '<div class="input-append date spDatePicker">';
		}
		$params = self::params( $params );
		$append = $bs3 ? ' data-append-to="body"' : null;
		$f .= '<input type="text" disabled="disabled"' . $append . ' value="' . $valueDisplay . '" ' . $params . ' name="' . $name . 'Holder" ' . $data . '/>';
		/**
		 * Mon, Nov 17, 2014 11:39:34 So here I am a bit baffled: we initially changed it to integer (unfortunately I do not remember why)
		 * but it seems that it may be overwriting the 32 bit (why a 64-bit machine limits integer to 32 bit is another story).
		 * I suppose it was to get rid of (possible) decimal place.
		 * So let's try another method!
		 * */
		if ( strstr( $value, '.' ) ) {
			$value = explode( '.', $value );
			$value = $value[ 0 ];
		}
		/** no offset, we are using UTC times only */
//		$value = ( $value ? ( ( $value + $offset ) * 1000 ) : null );
		$value = ( $value ? ( $value * 1000 ) : null );
		$f .= '<input type="hidden" value="' . $value . '" name="' . $name . '"/>';
//		$f .= '<input type="hidden" value="' . ( $value ? (int)( ( $value + SPFactory::config()->getTimeOffset() ) * 1000 ) : null ) . '" name="' . $name . '"/>';
//		$f .= '<input type="hidden" value="' . ( $value ? ( $value + SPFactory::config()->getTimeOffset() ) * 1000 : null ) . '" name="' . $name . '"/>';

		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$f .= '<span class="input-group-addon add-on">';
		}
		else {
			$f .= '<span class="add-on">';
		}
		$f .= '<i data-date-icon="icon-' . $icon . '" class="icon-' . $icon . '"></i></span>';
		$f .= '</div>';
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- Date Picker '{$name}' Output -->{$f}<!-- Date Picker '{$name}' End -->\n\n";
		return "{$f}\n";
	}

	/**
	 * @param array $data
	 *
	 * @return string
	 */
	public static function createDataTag( &$data )
	{
		if ( is_array( $data ) && isset( $data[ 'data' ] ) && count( $data[ 'data' ] ) ) {
			$tag = ' ';
			foreach ( $data[ 'data' ] as $name => $value ) {
				$name = SPLang::nid( preg_replace( '/(?<!^)([A-Z])/', '-\\1', $name ) );
				$tag .= "data-{$name}=\"{$value}\" ";
			}
			unset( $data[ 'data' ] );

			return $tag;
		}

		return null;
	}

	/**
	 * @param        $name - field name
	 * @param        $value - field value
	 * @param null $class
	 * @param string $dateFormat - date format in PHP
	 * @param null $params - additional parameters
	 *
	 * @internal param string $icon - field icon
	 * @return string
	 */
	public static function _dateGetter( $name, $value, $class = null, $dateFormat = 'Y-m-d H:i:s', $params = null )
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

		$f = '<div class="spOutput">';
		$f .= '<span ' . $params . '>' . $valueDisplay . '</span>';
		$f .= '</div>';

		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- Date Getter '{$name}' Output -->{$f}<!-- Date Getter '{$name}' End -->\n\n";
		return "{$f}\n";
	}

	public static function _createLangFile()
	{
		static $loaded = false;
		if ( !( $loaded ) ) {
			$lang = [
					'months' => Sobi::Txt( 'JS_CALENDAR_MONTHS' ),
					'monthsShort' => Sobi::Txt( 'JS_CALENDAR_MONTHS_SHORT' ),
					'days' => Sobi::Txt( 'JS_CALENDAR_DAYS' ),
					'daysShort' => Sobi::Txt( 'JS_CALENDAR_DAYS_SHORT' ),
					'daysMin' => Sobi::Txt( 'JS_CALENDAR_DAYS_MINI' ),
					'today' => Sobi::Txt( 'JS_CALENDAR_TODAY' ),
			];
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

	public static function _userSelector( $name, $value, $groups = null, $params = null, $icon = 'user', $header = 'USER_SELECT_HEADER', $format = '%user', $orderBy = 'id' )
	{
		static $count = 0;
		static $session = null;
		if ( !( $session ) ) {
			$session = SPFactory::user()->getUserState( 'userSelector', null, [] );
		}
		$params = self::checkArray( $params );
		if ( !( isset( $params[ 'id' ] ) ) ) {
			$params[ 'id' ] = SPLang::nid( $name );
		}
		$user = null;
		SPFactory::header()->addJsFile( 'user_selector' );
		$user = SPUser::getBaseData( ( int )$value );
		$settings = [
				'groups' => $groups,
				'format' => $format,
				'user' => Sobi::My( 'id' ),
				'ordering' => $orderBy,
				'time' => microtime( true ),
		];
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
			$replacements = [];
			preg_match_all( '/\%[a-z]*/', $format, $replacements );
			$placeholders = [];
			if ( isset( $replacements[ 0 ] ) && count( $replacements[ 0 ] ) ) {
				foreach ( $replacements[ 0 ] as $placeholder ) {
					$placeholders[] = str_replace( '%', null, $placeholder );
				}
			}
			if ( count( $replacements ) ) {
				foreach ( $placeholders as $attribute ) {
					if ( isset( $user->$attribute ) ) {
						$format = str_replace( '%' . $attribute, $user->$attribute, $format );
					}
				}
				$userData = str_replace( '"', null, $format );
			}
		}
		$modal = '<div class="response btn-group" data-toggle="buttons-radio"></div><br/><button class="btn btn-block hide more" type="button">' . Sobi::Txt( 'LOAD_MORE' ) . '</button>';
		$filter = '<input type="text" placeholder="' . Sobi::Txt( 'FILTER' ) . '" class="search pull-right spDisableEnter" name="q">';
		$id = $params[ 'id' ];
		$params = self::params( $params );
		$f = null;
		$f .= '<div class="spUserSelector">';
		$f .= '<div class="input-append">';
		$f .= '<input type="text" value="' . $userData . '" ' . $params . ' name="' . $name . 'Holder" readonly="readonly" class="trigger user-name"/>';
		$f .= '<span class="add-on trigger"><i class="icon-' . $icon . '"></i></span>';
		$f .= '</div>';
		$f .= '<input type="hidden" value="' . $value . '" name="' . $name . '" rel="selected"/>';
		$f .= '<input type="hidden" value="' . $ssid . '" name="' . $name . 'Ssid"/>';
		$f .= '<input type="hidden" value="1" name="' . SPFactory::mainframe()->token() . '"/>';
		$f .= "\n";
		$f .= self::modalWindow( Sobi::Txt( $header ) . $filter, $id . '-window', $modal );
		$f .= '</div>';
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- User Picker '{$name}' Output -->{$f}<!-- User Picker '{$name}' End -->\n\n";
		return "{$f}\n";
	}

	public static function _userGetter( $name, $value, $params = null, $class = null, $format = '%user' )
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
			$replacements = [];
			preg_match_all( '/\%[a-z]*/', $format, $replacements );
			$placeholders = [];
			if ( isset( $replacements[ 0 ] ) && count( $replacements[ 0 ] ) ) {
				foreach ( $replacements[ 0 ] as $placeholder ) {
					$placeholders[] = str_replace( '%', null, $placeholder );
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
		$f = '<div class="spOutput">';
		$f .= '<span ' . $params . '>' . $userData . '</span>';
		$f .= '</div>';
		Sobi::Trigger( 'Field', ucfirst( __FUNCTION__ ), [ &$f ] );

//		return "\n<!-- User Getter '{$name}' Output -->{$f}<!-- User Getter '{$name}' End -->\n\n";
		return "{$f}\n";
	}


	public static function _modalWindow( $header, $id = null, $content = null, $classes = 'modal hide', $closeText = 'CLOSE', $saveText = 'SAVE', $style = null )
	{
		$html = null;
		if ( $style ) {
			$style = " style=\"{$style}\"";
		}
		$bid = strlen( $id ) ? $id : md5( rand( 0, 10000 ) );
		$id = strlen( $id ) ? '" id="' . $id . '"' : null;

		$save = $saveText ? '<a href="#" id="' . $bid . '-save" class="btn btn-primary btn-sigsiu save" data-dismiss="modal">' . Sobi::Txt( $saveText ) . '</a>' : null;

		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			//remove hide from modal windows as BS3 modals aren't controlled via hide
			$classes = str_replace( 'hide', null, $classes );
		}
		$html .= '<div class="' . $classes . $id . $style . '>';
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$html .= '<div class="modal-dialog"><div class="modal-content">';
		}
		$html .= '  <div class="modal-header">
						<h3 class="modal-title">' . ( $header ) . '</h3>
					</div>
					<div class="modal-body">
					' . $content . '
					</div>
					<div class="modal-footer">
						<a href="#" class="btn btn-default" data-dismiss="modal">' . Sobi::Txt( $closeText ) . '</a>
						' . $save . '
					</div>';
		if ( Sobi::Cfg( 'template.bootstrap3-styles', true ) && !defined( 'SOBIPRO_ADM' ) ) {
			$html .= '</div></div>';
		}
		$html .= '</div>';

		return $html;
	}

	public static function _hidden( $name, $value = null, $id = null, $params = [] )
	{
		$data = self::createDataTag( $params );
		$id = $id ? $id : SPLang::nid( $name );
		$f = "<input type=\"hidden\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" {$data}/>";

//		return "\n<!--  '{$name}' Output -->{$f}<!-- '{$name}' End -->\n\n";
		return "{$f}\n";
	}
}
