<?php

/*
 * This file is part of the puli/repository package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Repository\Tests;

use Puli\Repository\Api\EditableRepository;
use Puli\Repository\Api\Resource\Resource;
use Puli\Repository\FilesystemRepository;
use Puli\Repository\Resource\DirectoryResource;
use Puli\Repository\Resource\FileResource;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Test\TestUtil;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FilesystemRepositoryCopyTest extends AbstractEditableRepositoryTest
{
    private $tempDir;

    protected function setUp()
    {
        $this->tempDir = TestUtil::makeTempDir('puli-repository', __CLASS__);

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $filesystem = new Filesystem();
        $filesystem->remove($this->tempDir);
    }

    protected function createPrefilledRepository(Resource $root)
    {
        $repo = new FilesystemRepository($this->tempDir, false);
        $repo->add('/', $root);

        return $repo;
    }

    protected function createWriteRepository()
    {
        return new FilesystemRepository($this->tempDir, false);
    }

    protected function createReadRepository(EditableRepository $writeRepo)
    {
        return new FilesystemRepository($this->tempDir, true, false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPassNonExistingBaseDirectory()
    {
        new FilesystemRepository($this->tempDir.'/foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPassFileAsBaseDirectory()
    {
        touch($this->tempDir.'/file');

        new FilesystemRepository($this->tempDir.'/file');
    }

    public function testGetFileLink()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('Symbolic links are not supported on some Windows enviroments.');
        }

        touch($this->tempDir.'/file');
        symlink($this->tempDir.'/file', $this->tempDir.'/link');

        $expected = new FileResource($this->tempDir.'/link', '/link');
        $expected->attachTo($this->writeRepo);

        $this->assertEquals($expected, $this->writeRepo->get('/link'));
    }

    public function testGetDirectoryLink()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('Symbolic links are not supported on some Windows enviroments.');
        }

        mkdir($this->tempDir.'/dir');
        symlink($this->tempDir.'/dir', $this->tempDir.'/link');

        $expected = new DirectoryResource($this->tempDir.'/link', '/link');
        $expected->attachTo($this->writeRepo);

        $this->assertEquals($expected, $this->writeRepo->get('/link'));
    }

    /**
     * @expectedException \Puli\Repository\Api\UnsupportedLanguageException
     * @expectedExceptionMessage foobar
     */
    public function testContainsFailsIfLanguageNotGlob()
    {
        $this->readRepo->contains('/*', 'foobar');
    }

    /**
     * @expectedException \Puli\Repository\Api\UnsupportedLanguageException
     * @expectedExceptionMessage foobar
     */
    public function testFindFailsIfLanguageNotGlob()
    {
        $this->readRepo->find('/*', 'foobar');
    }

    /**
     * @expectedException \Puli\Repository\Api\UnsupportedResourceException
     */
    public function testFailIfAddedResourceHasBodyAndChildren()
    {
        $resource = $this->getMock('Puli\Repository\Api\Resource\BodyResource');

        $resource->expects($this->any())
            ->method('hasChildren')
            ->will($this->returnValue(true));

        $this->writeRepo->add('/webmozart', $resource);
    }

    public function testAddDirectory()
    {
        $this->writeRepo->add('/webmozart/dir', new DirectoryResource(__DIR__.'/Fixtures/dir1'));

        $dir = $this->readRepo->get('/webmozart/dir');
        $file1 = $this->readRepo->get('/webmozart/dir/file1');
        $file2 = $this->readRepo->get('/webmozart/dir/file2');

        $this->assertInstanceOf('Puli\Repository\Resource\DirectoryResource', $dir);
        $this->assertInstanceOf('Puli\Repository\Resource\FileResource', $file1);
        $this->assertInstanceOf('Puli\Repository\Resource\FileResource', $file2);
    }

    public function testAddFile()
    {
        $this->writeRepo->add('/webmozart/file', new FileResource(__DIR__.'/Fixtures/dir1/file2'));

        $file = $this->readRepo->get('/webmozart/file');

        $this->assertInstanceOf('Puli\Repository\Resource\FileResource', $file);
    }

    /**
     * @expectedException \Puli\Repository\Api\UnsupportedOperationException
     */
    public function testFailIfAddingFileAsChildOfFile()
    {
        $this->writeRepo->add('/webmozart/puli', new FileResource(__DIR__.'/Fixtures/dir1/file1'));
        $this->writeRepo->add('/webmozart/puli/file', new FileResource(__DIR__.'/Fixtures/dir1/file2'));
    }

    /**
     * @expectedException \Puli\Repository\Api\UnsupportedLanguageException
     * @expectedExceptionMessage foobar
     */
    public function testRemoveFailsIfLanguageNotGlob()
    {
        $this->writeRepo->remove('/*', 'foobar');
    }
}
