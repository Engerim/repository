<?php

/*
 * This file is part of the Puli package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Puli\Locator;

/**
 * @since  %%NextVersion%%
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ResourceLocatorInterface
{
    /**
     * @param string $repositoryPath
     *
     * @return \Webmozart\Puli\Resource\ResourceInterface
     */
    public function getResource($repositoryPath);

    public function getResources($pattern);

    public function getTaggedResources($tag);

    public function listDirectory($repositoryPath);
}