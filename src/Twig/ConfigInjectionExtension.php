<?php

namespace App\Twig;

use Twig\TwigFunction;
use App\Entity\SiteConfig;
use Twig\Extension\AbstractExtension;
use Doctrine\ORM\EntityManagerInterface;

class ConfigInjectionExtension extends AbstractExtension
{
    private array $config = [];

    public function __construct(private readonly EntityManagerInterface $entityManager) 
    {
        $repository = $this->entityManager->getRepository(SiteConfig::class);
        $this->config = $repository->getAllConfigItems();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [$this, 'config']),
        ];
    }

    public function config(string $item)
    {
        $allowedArray = [
            'company_name',
            'company_motto',
            'company_address',
            'company_phone',
            'company_fax',
            'social_facebook',
            'social_twitter',
            'social_youtube',
            'social_instagram',
            'profile_allowUserEditing',
            'profile_allowManagerEditing',
        ];
        
        if (in_array($item, $allowedArray)) {
            return $this->config[$item];
        }

        return "<p class='text-warning'>The configuration item you passed is out of scope.</p>";
    }
}