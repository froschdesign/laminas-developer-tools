<?php

/**
 * @see       https://github.com/laminas/laminas-developer-tools for the canonical source repository
 * @copyright https://github.com/laminas/laminas-developer-tools/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-developer-tools/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\DeveloperTools;

use Laminas\Stdlib\AbstractOptions;

class Options extends AbstractOptions
{
    /**
     * @var ReportInterface
     */
    protected $report;

    /**
     * @var array
     */
    protected $profiler = array(
        'enabled'     => false,
        'strict'      => true,
        'flush_early' => false,
        'cache_dir'   => 'data/cache',
        'matcher'     => array(),
        'collectors' => array(
            'db'        => 'Laminas\DeveloperTools\DbCollector',
            'exception' => 'Laminas\DeveloperTools\ExceptionCollector',
            'request'   => 'Laminas\DeveloperTools\RequestCollector',
            'config'    => 'Laminas\DeveloperTools\ConfigCollector',
            'memory'    => 'Laminas\DeveloperTools\MemoryCollector',
            'time'      => 'Laminas\DeveloperTools\TimeCollector',
        ),
    );

    /**
     * Defaults for event-level logging
     * @var array
     */
    protected $events = array(
        'enabled'    => false,
        'collectors' => array(
            'memory' => 'Laminas\DeveloperTools\MemoryCollector',
            'time'   => 'Laminas\DeveloperTools\TimeCollector',
        ),
        'identifiers' => array(
        	'all' => '*'
        )
    );

    /**
     * @var array
     */
    protected $toolbar = array(
        'enabled'       => false,
        'auto_hide'     => false,
        'position'      => 'bottom',
        'version_check' => false,
        'entries'       => array(
            'request' => 'laminas-developer-tools/toolbar/request',
            'time'    => 'laminas-developer-tools/toolbar/time',
            'memory'  => 'laminas-developer-tools/toolbar/memory',
            'config'  => 'laminas-developer-tools/toolbar/config',
            'db'      => 'laminas-developer-tools/toolbar/db',
        ),
    );

    /**
     * Overloading Constructor.
     *
     * @param  array|Traversable|null $options
     * @param  ReportInterface        $report
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function __construct($options, ReportInterface $report)
    {
        $this->report = $report;

        parent::__construct($options);
    }

    /**
     * Sets Profiler options.
     *
     * @param array $options
     */
    public function setProfiler(array $options)
    {
        if (isset($options['enabled'])) {
            $this->profiler['enabled'] = (bool) $options['enabled'];
        }
        if (isset($options['strict'])) {
            $this->profiler['strict'] = (bool) $options['strict'];
        }
        if (isset($options['flush_early'])) {
            $this->profiler['flush_early'] = (bool) $options['flush_early'];
        }
        if (isset($options['cache_dir'])) {
            $this->profiler['cache_dir'] = (string) $options['cache_dir'];
        }
        if (isset($options['matcher'])) {
            $this->setMatcher($options['matcher']);
        }
        if (isset($options['collectors'])) {
            $this->setCollectors($options['collectors']);
        }
    }

    /**
     * Sets Event-level profiling options.
     *
     * @param array $options
     */
    public function setEvents(array $options)
    {
        if (isset($options['enabled'])) {
            $this->events['enabled'] = (bool) $options['enabled'];
        }
        if (isset($options['collectors'])) {
            $this->setEventCollectors($options['collectors']);
        }
        if (isset($options['identifiers'])) {
            $this->setEventIdentifiers($options['identifiers']);
        }
    }


    /**
     * Sets Profiler matcher options.
     *
     * @param array $options
     */
    protected function setMatcher($options)
    {
        if (!is_array($options)) {
            $this->report->addError(sprintf(
                '[\'laminas-developer-tools\'][\'profiler\'][\'matcher\'] must be an array, %s given.',
                gettype($options)
            ));

            return;
        }

        $this->profiler['matcher'] = $options;
    }

    /**
     * Sets Profiler collectors options.
     *
     * @param array $options
     */
    protected function setCollectors($options)
    {
        if (!is_array($options)) {
            $this->report->addError(sprintf(
                '[\'laminas-developer-tools\'][\'profiler\'][\'collectors\'] must be an array, %s given.',
                gettype($options)
            ));

            return;
        }

        foreach ($options as $name => $collector) {
            if (($collector === false || $collector === null)) {
                unset($this->profiler['collectors'][$name]);
            } else {
                $this->profiler['collectors'][$name] = $collector;
            }
        }
    }

    /**
     * Is the Profiler enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->profiler['enabled'];
    }

    /**
     * Sets Event-level collectors.
     *
     * @param array $options
     */
    public function setEventCollectors(array $options)
    {
        if (!is_array($options)) {
            $this->report->addError(sprintf(
                '[\'laminas-developer-tools\'][\'events\'][\'collectors\'] must be an array, %s given.',
                gettype($options)
            ));

            return;
        }

        foreach ($options as $name => $collector) {
            if (($collector === false || $collector === null)) {
                unset($this->events['collectors'][$name]);
            } else {
                $this->events['collectors'][$name] = $collector;
            }
        }
    }

    /**
     * Set Event-level collectors to listen to certain event identifiers. Defaults to '*' which causes the listener to
     * attach to all events.
     *
     * @param array $options
     */
    public function setEventIdentifiers(array $options)
    {
        if (!is_array($options)) {
            $this->report->addError(sprintf(
                '[\'laminas-developer-tools\'][\'events\'][\'identifiers\'] must be an array, %s given.',
                gettype($options)
            ));

            return;
        }

        foreach ($options as $name => $identifier) {
            if (($identifier === false || $identifier === null)) {
                unset($this->events['identifiers'][$name]);
            } else {
                $this->events['identifiers'][$name] = $identifier;
            }
        }
    }

    /**
     * Is the event-level statistics collection enabled?
     *
     * @return bool
     */
    public function eventCollectionEnabled()
    {
        return $this->events['enabled'];
    }

    /**
     * Is strict mode enabled?
     *
     * @return bool
     */
    public function isStrict()
    {
        return $this->profiler['strict'];
    }

    /**
     * Is it allowed to flush the page before the collector runs?
     *
     * Note: Only possible if the toolbar, firephp and the strict mode is
     *       disabled.
     *
     * @return bool
     */
    public function canFlushEarly()
    {
        return (
            $this->profiler['flush_early'] &&
            !$this->profiler['strict'] &&
            !$this->toolbar['enabled']
        );
    }

    /**
     * Returns the cache directory that is used to store the version cache or
     * any report storage that writes to the disk.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->profiler['cache_dir'];
    }

    // todo: getter for matcher

    /**
     * Returns the collectors.
     *
     * @return array
     */
    public function getCollectors()
    {
        return $this->profiler['collectors'];
    }

    /**
     * Returns the event-level collectors.
     *
     * @return array
     */
    public function getEventCollectors()
    {
        return $this->events['collectors'];
    }

    /**
     * Returns the event identifiers.
     *
     * @return array
     */
    public function getEventIdentifiers()
    {
        return $this->events['identifiers'];
    }


    /**
     * Sets Toolbar options.
     *
     * @param array $options
     */
    public function setToolbar(array $options)
    {
        if (isset($options['enabled'])) {
            $this->toolbar['enabled'] = (bool) $options['enabled'];
        }

        if (isset($options['version_check'])) {
            $this->toolbar['version_check'] = (bool) $options['version_check'];
        }
        if (isset($options['position'])) {
            if ($options['position'] !== 'bottom' && $options['position'] !== 'top') {
                $this->report->addError(sprintf(
                    '[\'laminas-developer-tools\'][\'toolbar\'][\'position\'] must be "top" or "bottom", %s given.',
                    $options['position']
                ));
            } else {
                $this->toolbar['position'] = $options['position'];
            }
        }
        if (isset($options['entries'])) {
            if (is_array($options['entries'])) {
                foreach ($options['entries'] as $collector => $template) {
                    if ($template === false || $template === null) {
                        unset($this->toolbar['entries'][$collector]);
                    } else {
                        $this->toolbar['entries'][$collector] = $template;
                    }
                }
            } else {
                $this->report->addError(sprintf(
                    '[\'laminas-developer-tools\'][\'toolbar\'][\'entries\'] must be an array, %s given.',
                    gettype($options['entries'])
                ));
            }
        }
    }

    /**
     * Is the Toolbar enabled?
     *
     * @return bool
     */
    public function isToolbarEnabled()
    {
        return $this->toolbar['enabled'];
    }

    /**
     * Is the Laminas version check enabled?
     *
     * @return bool
     */
    public function isVersionCheckEnabled()
    {
        return $this->toolbar['version_check'];
    }

    /**
     * Can hide Toolbar entries?
     *
     * @return bool
     */
    public function getToolbarAutoHide()
    {
        return $this->toolbar['auto_hide'];
    }

    /**
     * Returns the Toolbar position.
     *
     * @return array
     */
    public function getToolbarPosition()
    {
        return $this->toolbar['position'];
    }

    /**
     * Returns the Toolbar entries.
     *
     * @return array
     */
    public function getToolbarEntries()
    {
        return $this->toolbar['entries'];
    }

    // todo: storage and firephp options.
}