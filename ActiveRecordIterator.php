<?php
/**
 * Copyright (C) 2011 by allain@machete.ca
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class ActiveRecordIterator implements Iterator {
    const DEFAULT_BATCH_SIZE = 50;

    private $currentPage = 0;
    private $batchSize = 50;
    private $currentIndex = -1;
    private $totalRecordCount = -1;
    private $provider = null;
    private $records = null;    
    private $pagination = null;

    function __construct(CActiveDataProvider $activeDataProvider, $batchSize=ActiveRecordIterator::DEFAULT_BATCH_SIZE) {
        $this->provider = $activeDataProvider;                 
        $this->batchSize = intval($batchSize) ? intval($batchSize) : ActiveRecordIterator::DEFAULT_BATCH_SIZE;
        $this->pagination = $this->provider->getPagination();
        $this->pagination->setPageSize($batchSize);
        $this->totalRecordCount = $this->provider->getTotalItemCount();
    }

    public function current() {
        return $this->records[$this->currentIndex];
    }

    public function key() {
        return $this->currentPage * $this->batchSize + $this->currentIndex;
    }

    public function next() {
        $this->currentIndex++;
        if ($this->currentIndex >= $this->batchSize) {
            // Next Page
            $this->currentPage++;
            $this->currentIndex = 0;
            $this->loadPage();
        }
    }

    public function rewind() {
        $this->currentPage = 0;
        $this->currentIndex = 0;
        $this->loadPage();
    }

    private function loadPage() {
        $this->pagination->setCurrentPage($this->currentPage);
        
        $this->records = $this->provider->getData(true);
    }

    public function valid() {
        return $this->key() < $this->totalRecordCount;
    }
}
