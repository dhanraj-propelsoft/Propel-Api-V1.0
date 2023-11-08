<?php


namespace App\Http\Controllers\version1\Interfaces\Member;

interface MemberInterface
{
    public function findMemberByMobileNo($mobileNo);
    // public function findUserDataByEmail($data);
    public Function storeMember($model);
    public function findMemberDataByUid($uid);
    public function verifyMemberForMobile($data);
}
