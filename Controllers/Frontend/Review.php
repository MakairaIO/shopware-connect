<?php

use MakairaConnect\Controllers\Frontend\BaseFrontendController;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Repository;
use Shopware\Models\Article\Vote;

/**
 * This file is part of a makaira GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.makaira.io
 * @version    0.1
 * @author     Sunny <dt@marmalade.group>
 */
class Shopware_Controllers_Frontend_Review extends BaseFrontendController
{
    /**
     * @throws Exception
     */
    protected function getArticleReviews(): Enlight_Controller_Response_ResponseHttp
    {
        $requestParams = $this->getRequestParams();
        if (empty($requestParams['article_id'])) {
            return $this->createResponse([
                'ok' => false,
                'message' => 'article_id is required'
            ], 400);
        }
        /** @var Repository $articleRepo */
        $articleRepo = $this->get('models')->getRepository(Article::class);
        $article = $articleRepo->find($requestParams['article_id']);
        if ($article === NULL) {
            return $this->createResponse([
                'ok' => false,
                'message' => "Article with id {$requestParams['article_id']} not found"
            ], 400);
        }
        /** @var Article $article */
        $votes = $article->getVotes();
        $result = [];
        foreach ($votes as $vote) {
            /**@var Vote $vote */
            $result[] = [
                'active' => $vote->getActive(),
                'name' => $vote->getName(),
                'headline' => $vote->getHeadline(),
                'comment' => $vote->getComment(),
                'points' => $vote->getPoints(),
                'date' => $vote->getDatum()->format('Y-m-d H:i:s')
            ];
        }
        return $this->createResponse($result);
    }

    protected function createArticleReview(): Enlight_Controller_Response_ResponseHttp
    {
        try {
            $requestParams = $this->getRequestParams();
            if (empty($requestParams['article_id'])) {
                return $this->createResponse([
                    'ok' => false,
                    'message' => 'article_id is required'
                ], 400);
            }
            /** @var Repository $articleRepo */
            $articleRepo = $this->get('models')->getRepository(Article::class);
            $article = $articleRepo->find($requestParams['article_id']);
            if ($article === NULL) {
                return $this->createResponse([
                    'ok' => false,
                    'message' => "Article with id {$requestParams['article_id']} not found"
                ], 400);
            }

            $vote = new Vote();
            $vote->setArticle($article);
            $vote->setName($requestParams['name'] ?? '');
            $vote->setHeadline($requestParams['headline'] ?? '');
            $vote->setComment($requestParams['comment'] ?? '');
            $vote->setPoints($requestParams['points'] ?? '');
            $vote->setDatum(new DateTime());
            $vote->setActive(false);
            $vote->setEmail($requestParams['email'] ?? '');
            $vote->setAnswer('');

            $em = Shopware()->Container()->get(ModelManager::class);
            $em->persist($vote);
            $em->flush();

            return $this->createResponse([
                'ok' => true
            ]);
        } catch (Exception $e) {
            return $this->createResponse([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
