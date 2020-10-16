<?php declare(strict_types = 1);

namespace Pd\Version\Resolvers;

class AbsoluteUrlResolver extends \Pd\Version\Resolvers\AbstractPathResolver
{

	public function resolve(\Nette\Http\Url $url, string $directory, string $parameter): ?string
	{
		if ( ! $this->isAbsoluteUrl($url)) {
			return NULL;
		}

		if ( ! $this->cache) {
			return $this->process($url, $parameter);
		}

		return $this->cache->load([$url->path, $directory, $parameter], function () use ($url, $parameter): ?string {
			return $this->process($url, $parameter);
		});
	}


	private function process(\Nette\Http\Url $url, string $parameter): ?string
	{
		$version = NULL;
		$headers = @\get_headers($url->getAbsoluteUrl(), 1);
		if (\is_array($headers) && isset($headers['ETag'])) {
			$version = \preg_replace('~[^a-z0-9\-]~', '', $headers['ETag']);
		} elseif (\is_array($headers) && isset($headers['Last-Modified'])) {
			$version = (new \DateTime($headers['Last-Modified']))->getTimestamp();
		}

		return $this->getPath($url, $version, $parameter);
	}


	private function isAbsoluteUrl(\Nette\Http\Url $url): bool
	{
		if ( ! \Nette\Utils\Strings::length($url->getHost())) {
			return FALSE;
		}

		return $this->request && $url->getHost() !== $this->request->getUrl()->getHost();
	}

}
