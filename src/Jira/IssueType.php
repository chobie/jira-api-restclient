<?php
/*
 * The MIT License
 *
 * Copyright (c) 2014 Shuhei Tanuma
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace chobie\Jira;


/**
 * The issue type.
 *
 * @method integer getAvatarId() Gets avatar ID.
 * @method string getDescription() Gets description.
 * @method string getEntityId() Gets Unique ID for next-gen projects.
 * @method integer getHierarchyLevel() Gets hierarchy level.
 * @method string getIconUrl() Gets icon url.
 * @method string getId() Gets ID.
 * @method string getName() Gets name.
 * @method string getUntranslatedName() Gets untranslated name.
 * @method array getScope() Gets details of the next-gen projects the issue type is available in.
 * @method string getSelf() Gets the URL of these issue type details.
 */
class IssueType
{

	/**
	 * Data.
	 *
	 * @var array
	 */
	private $_data;

	/**
	 * Acceptable keys.
	 *
	 * @var array
	 */
	private $_acceptableKeys = array(
		'avatarId',
		'description',
		'entityId',
		'hierarchyLevel',
		'iconUrl',
		'id',
		'name',
		'scope',
		'self',
		'subtask',

		'untranslatedName',
	);

	/**
	 * Creates issue instance.
	 *
	 * @param array $data Data.
	 *
	 * @throws \Exception When an unknown data field is given.
	 */
	public function __construct(array $data)
	{
		$unknown_fields = array_diff(array_keys($data), $this->_acceptableKeys);

		if ( $unknown_fields ) {
			throw new \Exception(
				'The "' . implode('", "', $unknown_fields) . '" issue type keys are not supported.'
			);
		}

		$this->_data = $data;
	}

	/**
	 * Gets sub-task.
	 *
	 * @return boolean
	 */
	public function isSubtask()
	{
		return $this->_data['subtask'];
	}

	/**
	 * Allows accessing issue type properties.
	 *
	 * @param string $method Method name.
	 * @param array  $params Params.
	 *
	 * @return mixed
	 * @throws \Exception When requested method wasn't found.
	 */
	public function __call($method, array $params)
	{
		if ( preg_match('/^get(.+)$/', $method, $regs) ) {
			$data_key = lcfirst($regs[1]);

			if ( in_array($data_key, $this->_acceptableKeys) ) {
				return array_key_exists($data_key, $this->_data) ? $this->_data[$data_key] : null;
			}
		}

		throw new \Exception('The "' . __CLASS__ . '::' . $method . '" method does not exist.');
	}

}
