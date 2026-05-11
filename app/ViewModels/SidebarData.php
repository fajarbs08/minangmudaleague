<?php

namespace App\ViewModels;

class SidebarData
{
    public function __construct(
        private readonly array $adminPendingCounts,
        private readonly array $navState,
    ) {}

    public function isActive(string $key): bool
    {
        return (bool) ($this->navState[$key] ?? false);
    }

    public function activeClass(string $key): string
    {
        return $this->isActive($key) ? 'active' : '';
    }

    public function isExpanded(string $key): bool
    {
        return $this->isActive($key);
    }

    public function ariaExpanded(string $key): string
    {
        return $this->isExpanded($key) ? 'true' : 'false';
    }

    public function collapseClass(string $key): string
    {
        return $this->isExpanded($key) ? 'show' : '';
    }

    public function pendingCount(string $key): int
    {
        return (int) ($this->adminPendingCounts[$key] ?? 0);
    }
}
