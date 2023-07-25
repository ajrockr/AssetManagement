<?php

namespace App\Plugin;

interface AssetInterface
{
    public function setAssetTag(string $assetTag): self;

    public function getAssetTag(): ?string;
}