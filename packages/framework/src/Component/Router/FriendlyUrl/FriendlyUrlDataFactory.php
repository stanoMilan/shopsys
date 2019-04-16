<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlDataFactory implements FriendlyUrlDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    public function create(): FriendlyUrlData
    {
        return new FriendlyUrlData();
    }
}
