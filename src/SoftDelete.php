<?php

namespace phongbui\yii2trait;

trait SoftDelete
{
    public $deleted_at = 'deleted_at';

    /**
     * @return mixed
     */
    public function softDelete()
    {
        $this->{$this->deleted_at} = date('Y-m-d H:i:s');
        return $this->save();
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
}