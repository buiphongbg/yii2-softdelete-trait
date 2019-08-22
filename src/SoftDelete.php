<?php

namespace phongbui\yii2trait;

use yii\db\Expression;

trait SoftDelete
{
    /**
     * @return mixed
     */
    public function softDelete()
    {
        return $this->updateAttributes([
            $this->getDeletedAtColumn() => new Expression('NOW()')
        ]);
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function delete()
    {
        if (!$this->isTransactional(self::OP_DELETE)) {
            return $this->softDelete();
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->softDelete();
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }

            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function find()
    {
        return parent::find()->andWhere('deleted_at is NULL');
    }

    /**
     * Get the name of the "deleted at" column.
     *
     * @return string
     */
    public function getDeletedAtColumn()
    {
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }
}