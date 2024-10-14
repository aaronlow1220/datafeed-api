<?php

namespace v1\components;

use yii\rest\Serializer;

/**
 * Serializer.
 */
class ApiSerializer extends Serializer
{
    /**
     * Serializes a pagination into an array.
     *
     * @param Pagination $pagination
     * @return array<string, array<string, int>> the array representation of the pagination
     *
     * @see addPaginationHeaders()
     */
    protected function serializePagination($pagination): array
    {
        return [
            $this->metaEnvelope => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'pageSize' => $pagination->getPageSize(),
            ],
        ];
    }

    /**
     * {@inherit}.
     *
     * @return array<string[]>
     */
    protected function getRequestedFields(): array
    {
        list($fields, $expand) = parent::getRequestedFields();
        $requestParams = $this->request->getBodyParams();
        $rpFields = $requestParams[$this->fieldsParam] ?? null;
        $rpExpand = $requestParams[$this->expandParam] ?? null;

        if (null !== $rpFields) {
            if (is_array($rpFields)) {
                $fields = $rpFields;
            } elseif (is_string($rpFields)) {
                $fields = preg_split('/\s*,\s*/', $rpFields, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $fields = [];
            }
        }

        if (null !== $rpExpand) {
            if (is_array($rpExpand)) {
                $expand = $rpExpand;
            } elseif (is_string($rpExpand)) {
                $expand = preg_split('/\s*,\s*/', $rpExpand, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $expand = [];
            }
        }

        return [
            $fields,
            $expand,
        ];
    }
}
