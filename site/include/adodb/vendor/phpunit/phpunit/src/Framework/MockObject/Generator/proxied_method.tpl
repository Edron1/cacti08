
    [modifier] function [reference][method_name]([arguments_decl])[return_declaration]
    {
        $__phpunit_arguments = [[arguments_call]];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > [arguments_count]) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = [arguments_count]; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                '[class_name]', '[method_name]', $__phpunit_arguments, '[return_type]', $this, [clone_arguments], true
            )
        );

        return call_user_func_array(array($this->__phpunit_originalObject, "[method_name]"), $__phpunit_arguments);
    }
