<?php

namespace Ferga\FilamentBuilder\Templates;

class TemplateRegistry
{
    /** @var array<string, Template> */
    protected array $templates = [];

    public function register(Template $template): static
    {
        $this->templates[$template->key] = $template;

        return $this;
    }

    /** @param  array<int, Template>  $templates */
    public function registerMany(array $templates): static
    {
        foreach ($templates as $template) {
            $this->register($template);
        }

        return $this;
    }

    public function set(array $templates): static
    {
        $this->templates = [];

        return $this->registerMany($templates);
    }

    public function forget(string $key): static
    {
        unset($this->templates[$key]);

        return $this;
    }

    public function get(?string $key): ?Template
    {
        return $key === null ? null : ($this->templates[$key] ?? null);
    }

    /** @return array<string, Template> */
    public function all(): array
    {
        return $this->templates;
    }

    /** @return array<string, Template> */
    public function sections(): array
    {
        return array_filter($this->templates, fn (Template $template) => ! $template->isPage());
    }

    /** @return array<string, Template> */
    public function pages(): array
    {
        return array_filter($this->templates, fn (Template $template) => $template->isPage());
    }

    /** Grouped options for a select: [group label => [key => label]]. */
    public function options(): array
    {
        $options = [];

        foreach (['sections' => $this->sections(), 'pages' => $this->pages()] as $group => $templates) {
            foreach ($templates as $key => $template) {
                $options[__("ferga-builder::templates.groups.{$group}")][$key] = $template->getLabel();
            }
        }

        return $options;
    }
}
