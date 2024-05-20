<?php

namespace vaersaagod\muxmate\gql;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use vaersaagod\muxmate\helpers\MuxMateHelper;

use yii\base\InvalidConfigException;

class MuxMateFieldResolver extends ObjectType
{
    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo): mixed
    {
        $fieldName = $resolveInfo->fieldName;

        if (!isset($source->muxMetaData) || isset($source->muxMetaData[$fieldName])) {
            return null;
        }


        switch ($fieldName) {
            case 'error':
                return $source->muxMetaData['errors']['type'] ?? null;
            case 'playback_id':
                if (!isset($source->muxMetaData['playback_ids'])) return null;

                $policy = $arguments['policy'] ?? null;
                if (!$policy && isset($source->muxMetaData['playback_ids'])) {
                    return $source->muxMetaData['playback_ids'][0]['id'];
                }
                if (isset($policy) && !in_array($policy, [MuxMateHelper::PLAYBACK_POLICY_SIGNED, MuxMateHelper::PLAYBACK_POLICY_PUBLIC], true)) {
                    throw new InvalidConfigException("Invalid playback policy \"$policy\"");
                }
                $index = array_search($policy, array_column($source->muxMetaData['playback_ids'], 'policy'));
                return $source->muxMetaData['playback_ids'][$index]['id'] ?? null;
            default:
                return $source->muxMetaData[$fieldName] ?? $source->$fieldName ?? null;
        }
    }
}
