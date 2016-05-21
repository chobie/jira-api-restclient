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
namespace chobie\Jira\Api\Authentication;


class Basic implements AuthenticationInterface
{

	/**
	 * User ID.
	 *
	 * @var string
	 */
	private $_userId;

	/**
	 * Password.
	 *
	 * @var string
	 */
	private $_password;

	/**
	 * Creates class instance.
	 *
	 * @param string $user_id  User ID.
	 * @param string $password Password.
	 */
	public function __construct($user_id, $password)
	{
		$this->_userId = $user_id;
		$this->_password = $password;
	}

	/**
	 * Returns credential string.
	 *
	 * @return string
	 */
	public function getCredential()
	{
		return base64_encode($this->_userId . ':' . $this->_password);
	}

	/**
	 * Returns user id.
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->_userId;
	}

	/**
	 * Returns password.
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->_password;
	}

}
