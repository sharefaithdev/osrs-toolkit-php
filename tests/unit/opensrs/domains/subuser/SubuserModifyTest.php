<?php

use opensrs\domains\subuser\SubuserModify;

/**
 * @group subuser
 * @group SubuserModify
 */
class SubuserModifyTest extends PHPUnit_Framework_TestCase
{
    protected $func = 'subuserModify';

    protected $validSubmission = array(
        'cookie' => '',

        'attributes' => array(
            /*
             * Required: 1 of 2
             *
             * cookie: domain auth cookie
             * domain: relevant domain, required
             *   only if cookie is not sent
             */
            'domain' => '',

            /*
             * Required
             *
             * username: parent user's username
             * sub_username: username for the sub-user
             * sub_permission: bit-mask indicating
             *   which portions of the domain information
             *   are changeable by the sub-user, bits
             *   are as follows:
             *     - 1 = Owner
             *     - 2 = Admin
             *     - 4 = Billing
             *     - 8 = Tech
             *     - 16 = Nameservers
             *     - 32 = Rsp_whois_info
             * sub_password: password for the sub-user
             * sub_id: the ID of the sub-user to
             *   modify
             */
            'username' => '',
            'sub_username' => '',
            'sub_permission' => '',
            'sub_password' => '',
            'sub_id' => '',
            ),
        );

    /**
     * Valid submission should complete with no
     * exception thrown.
     *
     *
     * @group validsubmission
     */
    public function testValidSubmission()
    {
        $data = json_decode(json_encode($this->validSubmission));

        $data->cookie = md5(time());

        $data->attributes->username = 'phptest'.time();
        $data->attributes->sub_username = 'phptestuser';
        $data->attributes->sub_permission = '2';
        $data->attributes->sub_password = 'password1234';
        $data->attributes->sub_id = time();

        $ns = new SubuserModify('array', $data);

        $this->assertTrue($ns instanceof SubuserModify);
    }

    /**
     * Data Provider for Invalid Submission test.
     */
    public function submissionFields()
    {
        return array(
            'missing cookie' => array('cookie', null),
            'missing username' => array('username'),
            'missing sub_username' => array('sub_username'),
            'missing sub_permission' => array('sub_permission'),
            'missing sub_password' => array('sub_password'),
            'missing sub_id' => array('sub_id'),
            );
    }

    /**
     * Invalid submission should throw an exception.
     *
     *
     * @dataProvider submissionFields
     * @group invalidsubmission
     */
    public function testInvalidSubmissionFieldsMissing($field, $parent = 'attributes', $message = null)
    {
        $data = json_decode(json_encode($this->validSubmission));

        $data->cookie = md5(time());

        $data->attributes->username = 'phptest'.time().'.com';
        $data->attributes->sub_username = 'phptestuser'.time();
        $data->attributes->sub_permission = '2';
        $data->attributes->sub_password = 'password1234';
        $data->attributes->sub_id = time();

        if (is_null($message)) {
            $this->setExpectedExceptionRegExp(
              'opensrs\Exception',
              "/$field.*not defined/"
              );
        } else {
            $this->setExpectedExceptionRegExp(
              'opensrs\Exception',
              "/$message/"
              );
        }

        // clear field being tested
        if (is_null($parent)) {
            unset($data->$field);
        } else {
            unset($data->$parent->$field);
        }

        $ns = new SubuserModify('array', $data);
    }

    /**
     * Invalid submission should throw an exception.
     *
     *
     * @group invalidsubmission
     */
    public function testInvalidSubmissionCookieAndDomainSent()
    {
        $data = json_decode(json_encode($this->validSubmission));

        $data->cookie = md5(time());
        $data->attributes->domain = 'phptest'.time().'.com';

        $data->attributes->username = 'phptest'.time().'.com';
        $data->attributes->sub_username = 'phptestuser'.time();
        $data->attributes->sub_permission = '2';
        $data->attributes->sub_password = 'password1234';
        $data->attributes->sub_id = time();

        $this->setExpectedExceptionRegExp(
            'opensrs\Exception',
            '/.*cookie.*domain.*cannot.*one.*call.*/'
            );

        $ns = new SubuserModify('array', $data);
    }
}
