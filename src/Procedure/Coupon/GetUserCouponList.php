<?php

namespace Tourze\CouponCoreBundle\Procedure\Coupon;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '获取用户的所有优惠券')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetUserCouponList')]
class GetUserCouponList extends BaseProcedure
{
    use PaginatorTrait;

    #[MethodParam(description: '指定优惠券ID列表')]
    public array $couponIds = [];

    #[MethodParam(description: '状态，1待使用、2已使用、3已过期')]
    public int $status = 0;

    public function __construct(
        private readonly CouponRepository $couponRepository,
        private readonly CodeRepository $codeRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $coupons = [];
        if ([] !== $this->couponIds) {
            $coupons = $this->couponRepository->findBy([
                'id' => $this->couponIds,
            ]);
            if ([] === $coupons) {
                // 如果指定的优惠券ID不存在，返回空结果
                return $this->fetchList($this->codeRepository->createQueryBuilder('a')->where('a.id = 0'), $this->formatItem(...));
            }
        }

        $user = $this->security->getUser();
        if (null === $user) {
            throw new \RuntimeException('User not authenticated');
        }
        $qb = $this->codeRepository->createUserCouponCodesQueryBuilder(
            $user,
            $coupons,
            $this->status
        );

        return $this->fetchList($qb, $this->formatItem(...));
    }

    /**
     * @return array<string, mixed>
     */
    private function formatItem(Code $item): array
    {
        $normalized = $this->normalizer->normalize($item, 'array', ['groups' => 'restful_read']);
        if (!is_array($normalized)) {
            throw new \RuntimeException('Normalization failed');
        }

        /** @var array<string, mixed> $normalized */
        return $normalized;
    }
}
