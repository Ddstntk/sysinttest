<?php
/**
 * Tags data transformer.
 */
namespace Form;

use Repository\TagsRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class TagsDataTransformer.
 */
class TagsDataTransformer implements DataTransformerInterface
{
    /**
     * Tags repository.
     *
     * @var TagsRepository|null $tagsRepository
     */
    protected $tagsRepository = null;

    /**
     * TagsDataTransformer constructor.
     *
     * @param TagsRepository $tagsRepository Tags repository
     */
    public function __construct(TagsRepository $tagsRepository)
    {
        $this->tagsRepository = $tagsRepository;
    }

    /**
     * Transform array of tags Ids to string of names.
     *
     * @param array $tags Tags ids
     *
     * @return string Result
     */
    /**
     * Transform array of tags Ids to string of names.
     *
     * @param array $tags Tags ids
     *
     * @return string Result
     */
    public function transform($tags)
    {
        if (null == $tags) {
            return '';
        }

        $tagNames = [];

        foreach ($tags as $tag) {
            $tagNames[] = $tag['name'];
        }

        return implode(',', $tagNames);
    }

    /**
     * Transform string of tag names into array of Tags Ids.
     *
     * @param string $string String of tag names
     *
     * @return array Result
     */
    public function reverseTransform($string)
    {
        $tagNames = explode(',', $string);

        $tags = [];
        foreach ($tagNames as $tagName) {
            if (trim($tagName) !== '') {
                $tag = $this->tagsRepository->findOneByName($tagName);
                if (null === $tag || !count($tag)) {
                    $tag = [];
                    $tag['name'] = $tagName;
                    $tag = $this->tagsRepository->save($tag);
                }
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
