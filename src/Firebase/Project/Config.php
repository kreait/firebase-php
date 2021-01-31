<?php

declare(strict_types=1);

namespace Kreait\Firebase\Project;

interface Config
{
    public function defaultDatabaseUrl(): ?string;

    public function defaultDynamicLinksDomain(): ?string;

    public function serviceAccount(): ?string;
}
