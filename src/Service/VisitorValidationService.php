<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
// use Tourze\BizUserBundle\Entity\BizUser; // 暂时注释，避免依赖问题
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;
use Tourze\VisitorManageBundle\DTO\VisitorSearchCriteria;
use Tourze\VisitorManageBundle\Exception\InvalidVisitorDataException;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

readonly class VisitorValidationService
{
    private const MOBILE_PATTERN = '/^1[3-9]\d{9}$/';
    private const MAX_VEHICLE_NUMBER_LENGTH = 20;
    private const REQUIRED_APPROVAL_ROLE = 'ROLE_VISITOR_APPROVER';

    public function __construct(
        private ValidatorInterface $validator,
        private VisitorRepository $visitorRepository,
    ) {
    }

    public function validateRegistrationData(VisitorRegistrationData $data): bool
    {
        $violations = $this->validator->validate($data);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = (string) $violation->getMessage();
            }
            throw new InvalidVisitorDataException($errors);
        }

        return true;
    }

    public function validateMobileFormat(string $mobile): bool
    {
        return 1 === preg_match(self::MOBILE_PATTERN, $mobile);
    }

    public function validateVehicleNumber(?string $vehicleNumber): bool
    {
        // 车牌号为可选字段
        if (null === $vehicleNumber) {
            return true;
        }

        // 不允许空字符串，但允许null
        if ('' === $vehicleNumber) {
            return false;
        }

        // 车牌号长度验证（考虑中文字符）
        return mb_strlen($vehicleNumber, 'UTF-8') <= self::MAX_VEHICLE_NUMBER_LENGTH;
    }

    public function validateAppointmentTime(\DateTime $appointmentTime): bool
    {
        $now = new \DateTime();

        return $appointmentTime > $now;
    }

    public function validateApprovalPermission(object $bizUser): bool
    {
        if (method_exists($bizUser, 'hasRole')) {
            return (bool) $bizUser->hasRole(self::REQUIRED_APPROVAL_ROLE);
        }

        return false;
    }

    public function validateSearchCriteria(VisitorSearchCriteria $criteria): bool
    {
        return $this->isValidPagination($criteria->page, $criteria->limit)
            && $this->isValidDateRange($criteria->appointmentFrom, $criteria->appointmentTo);
    }

    private function isValidPagination(int $page, int $limit): bool
    {
        return $page > 0 && $limit > 0;
    }

    private function isValidDateRange(?\DateTime $from, ?\DateTime $to): bool
    {
        if (null === $from || null === $to) {
            return true;
        }

        return $from <= $to;
    }

    public function validateVisitorExists(int $visitorId): bool
    {
        $visitor = $this->visitorRepository->find($visitorId);

        return null !== $visitor;
    }
}
