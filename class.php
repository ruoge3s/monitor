<?php

/**
 * Class Filter
 */
class Filter extends RecursiveFilterIterator
{
    public function accept()
    {
        if ($this->current()->isDir()) {
            if (preg_match('/^\./', $this->current()->getFilename())) {
                return false;
            }
            return !in_array($this->current()->getFilename(), config('watch.exclude'));
        }
        $list = implode('|', config('watch.ext'));
        return preg_match("/($list)$/", $this->current()->getFilename());
    }
}
