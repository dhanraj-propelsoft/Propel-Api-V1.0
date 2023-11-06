<?php

namespace App\Http\Controllers\version1\Services\Member;

use App\Http\Controllers\version1\Interfaces\Member\MemberInterface;
use App\Http\Controllers\version1\Interfaces\Organization\OrganizationInterface;
use App\Http\Controllers\version1\Interfaces\Person\PersonInterface;
use App\Http\Controllers\version1\Services\Common\CommonService;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MemberService
{
    public function __construct(MemberInterface $MemberInterface, PersonInterface $personInterface, CommonService $commonService, OrganizationInterface $OrganizationInterface)
    {
        $this->MemberInterface = $MemberInterface;
        $this->personInterface = $personInterface;
        $this->commonService = $commonService;
        $this->OrganizationInterface = $OrganizationInterface;
    }
    public function storeMember($data)
    {

        Log::info('MemberService > storeMember function Inside.' . json_encode($data));
        $validator = Validator::make($data, [
            'password' => 'required|string|max:255',
            'passwordConfirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $datas = (object) $data;
        $personModel = $this->personInterface->getPrimaryMobileAndEmailbyUid($datas->uid);
        $personData = ['mobile' => $personModel->mobile->mobile_no, 'email' => $personModel->email->email];
        $model = $this->convertToMemberModel($personData, $datas);
        $storeMember = $this->MemberInterface->storeMember($model);
        Log::info('MemberService > storeMember function Return.' . json_encode($storeMember));

        if ($storeMember['message'] == "Success") {

            return $this->commonService->sendResponse($storeMember['data'], $storeMember['message']);
        } else {
            return $this->commonService->sendError($storeMember['data'], $storeMember['message']);
        }
    }
    public function convertToMemberModel($personData, $datas)
    {
        Log::info('MemberService -> convertToMemberModel  function Inside.' . json_encode($datas));
        Log::info('MemberService -> convertToMemberModel  function Inside.' . json_encode($personData));
        $personData = (object) $personData;
        $model = new Member();
        $model->uid = $datas->uid;
        $model->primary_email = $personData->email;
        $model->primary_mobile = $personData->mobile;
        $model->password = Hash::make($datas->password);
        $model->pfm_stage_id = 1;
        $model->pfm_active_status_id = 1;
        Log::info('MemberService > convertToMemberModel function Return.' . json_encode($model));
        return $model;
    }
    public function setPasswordForMember($datas)
    {
        $validator = Validator::make($datas, [
            'password' => 'required|string|max:255',
            'passwordConfirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        Log::info('MemberService > setPasswordForMember function Inside.' . json_encode($datas));
        $datas = (object) $datas;
        $member = $this->MemberInterface->findMemberDataByUid($datas->uid);
        Log::info('MemberService > setPasswordForMember function Return.' . json_encode($member));
        if ($member) {
            $password = Hash::make($datas->password);
            $member->password = $password;
            $model = $this->MemberInterface->storeMember($member);
            if ($model['message'] == "Success") {
                $memberModel = $model['data'];
                $personModel = $this->personInterface->getPersonPrimaryDataByUid($memberModel->uid);
                return $this->commonService->sendResponse($personModel, $model['message']);
            } else {
                return $this->commonService->sendError($model['data'], $model['message']);
            }
        }

    }
    public function passwordUpdateForMember($datas)
    {
        $validator = Validator::make($datas, [
            'password' => 'required|string|max:255',
            'passwordConfirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        Log::info('MemberService > passwordUpdateForMember function Inside.' . json_encode($datas));
        $datas = (object) $datas;
        $member = $this->MemberInterface->findMemberDataByUid($datas->uid);
        Log::info('MemberService > passwordUpdateForMember function Return.' . json_encode($member));
        if (Hash::check($datas->oldPassword, $member->password)) {
                $password = Hash::make($datas->password);
                $member->password = $password;
                $model = $this->MemberInterface->storeMember($member);
        } else {
            $model = ['message' => 'Failed', 'status' => 'old Password MisMatched'];
        }
        return $this->commonService->sendResponse($model, '');
    }
    public function memberLogin($datas)
    {
        $validator = Validator::make($datas, [
            'memberName' => 'required|string|max:255',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        Log::info('MemberService > memberLogin function Inside.' . json_encode($datas));
        $datas = (object) $datas;
        $verifyMember = $this->MemberInterface->verifyMemberForMobile($datas);
        Log::info('MemberService > memberLogin function Return.' . json_encode($verifyMember));
        if ($verifyMember) {
            $uid = $verifyMember->uid;
            $personDetail = $this->personInterface->getPersonPicAndPersonName($uid);
            $nickName = $personDetail->nick_name ?? null;
            $firstName = $personDetail->first_name ?? null;
            $personPic = $personDetail->PersonPic->profile_pic ?? null;
            if (Hash::check($datas->password, $verifyMember->password)) {
                $token = $verifyMember->createToken('Laravel Password Grant Client')->accessToken;
                $personStatus = $this->personInterface->checkPersonExistence($uid);
                $personType = $personStatus ? $personStatus->existence : null;
                $defaultOrg = $this->OrganizationInterface->getPerviousDefaultOrganization($uid);
                $response = ['personType' => $personType, 'token' => $token, 'uid' => $uid, 'defaultOrg' => $defaultOrg, 'nickName' => $nickName, 'firstName' => $firstName, 'personPic' => $personPic];
                return $this->commonService->sendResponse($response, "");
            } else {
                $response = ["message" => "Password mismatch", 'firstName' => $firstName, 'uid' => $uid];
                return response($response, 422);
            }
        } else {
            $response = ["message" => 'Member does not exist'];
            return response($response, 422);
        }
    }
}
