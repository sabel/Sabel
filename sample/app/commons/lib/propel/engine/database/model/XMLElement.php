<?php
/*
 *  $Id: XMLElement.php 64 2005-05-13 02:43:56Z root $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://propel.phpdb.org>.
 */

/**
 * An abstract class for elements represented by XML tags (e.g. Column, Table).
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @version $Revision: 64 $
 * @package propel.engine.database.model
 */
abstract class XMLElement {
	
	protected $attributes = array();
	
	/**
	 * Replaces the old loadFromXML() so that we can use loadFromXML() to load the attribs into the class.
	 */
	abstract protected function setupObject();
	
	/**
	 * This is the entry point method for loading data from XML.
	 * It calls a setupObject() method that must be implemented by the child class.
	 * @param array $attributes The attributes for the XML tag.
	 */
	public function loadFromXML($attributes) {
		$this->attributes = array_change_key_case($attributes, CASE_LOWER);
		$this->setupObject();
	}
	
	/**
	 * Returns the assoc array of attributes.
	 * All attribute names (keys) are lowercase.
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}
	
	/**
	 * Gets a particular attribute by [case-insensitive] name.
	 * If attribute is not set then the $defaultValue is returned.
	 * @param string $name The [case-insensitive] name of the attribute to lookup.
	 * @param mixed $defaultValue The default value to use in case the attribute is not set.
	 * @return mixed The value of the attribute or $defaultValue if not set.
	 */
	public function getAttribute($name, $defaultValue = null) {
		$name = strtolower($name);
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		} else {
			return $defaultValue;
		}
	}
	
	/**
     * Converts value specified in XML to a boolean value.
     * This is to support the default value when used w/ a boolean column.
     * @return value
     */
    protected function booleanValue($val) {
        if (is_numeric($val)) {
            return (bool) $val;
        } else {
            return (in_array(strtolower($val), array('true', 't', 'y', 'yes'), true) ? true : false);
        }
    }
}