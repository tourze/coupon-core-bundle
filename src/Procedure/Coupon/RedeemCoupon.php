<?php

namespace Tourze\CouponCoreBundle\Procedure\Coupon;

use HttpClientBundle\Exception\HttpClientException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CouponCoreBundle\Exception\CodeUsedException;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag('优惠券模块')]
#[MethodDoc('使用优惠券')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('RedeemCoupon')]
#[Log]
#[WithMonologChannel('procedure')]
class RedeemCoupon extends LockableProcedure
{
    /**
     * @var int 优惠券类型
     */
    public int $type = 0;

    /**
     * @var array 券码
     */
    public array $code = [];

    /**
     * @var string 订单号
     */
    public string $orderId = '';

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly CouponService $codeService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): array
    {
        $errorMessage = '';
        foreach ($this->code as $item) {
            $code = $this->codeRepository->findOneBy([
                'sn' => $item,
            ]);
            if (empty($code)) {
                throw new ApiException('券码不存在');
            }

            try {
                $this->codeService->redeemCode($code);
            } catch (CodeUsedException) {
                $errorMessage = "{$code->getCoupon()->getName()}已被使用";
            } catch (HttpClientException $exception) {
                $this->logger->error('远程核销失败', [
                    'code' => $item,
                    'exception' => $exception,
                ]);
            } catch (\Throwable $exception) {
                $this->logger->error('核销失败', [
                    'code' => $item,
                    'exception' => $exception,
                ]);
                $errorMessage = "{$code->getCoupon()->getName()}核销失败";
            }
        }

        if ($errorMessage) {
            throw new ApiException($errorMessage);
        }

        $result = [];
        $result['__message'] = '使用成功';

        return $result;
    }
}
