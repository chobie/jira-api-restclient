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


class IssueType
{

	/**
	 * Self.
	 *
	 * @var string
	 */
	protected $self;

	/**
	 * ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Icon URL.
	 *
	 * @var string
	 */
	protected $iconUrl;

	/**
	 * Name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Sub-task.
	 *
	 * @var string
	 */
	protected $subTask;

	/**
	 * Avatar ID.
	 *
	 * @var string
	 */
	protected $avatarId;

	/**
	 * Acceptable keys.
	 *
	 * @var array
	 */
	private $_acceptableKeys = array(
		'self',
		'id',
		'description',
		'iconUrl',
		'name',
		'subtask',
		'avatarId',
	);

	/**
	 * Creates issue instance.
	 *
	 * @param array $types Types.
	 *
	 * @throws \Exception When unknown type is given.
	 */
	public function __construct(array $types)
	{
		foreach ( $types as $key => $value ) {
			if ( in_array($key, $this->_acceptableKeys) ) {
				$this->$key = $value;
			}
			else {
				throw new \Exception('the key ' . $key . ' does not support');
			}
		}
	}

	/**
	 * Gets name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Gets sub-task.
	 *
	 * @return string
	 */
	public function isSubtask()
	{
		return $this->subTask;
	}

	/**
	 * Gets ID.
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Gets description.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Gets icon url.
	 *
	 * @return string
	 */
	public function getIconUrl()
	{
		return $this->iconUrl;
	}

	/**
	 * Gets avatar id.
	 *
	 * @return string
	 */
	public function getAvatarId()
	{
		return $this->avatarId;
	}

}
