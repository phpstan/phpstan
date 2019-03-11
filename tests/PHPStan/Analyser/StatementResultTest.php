<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Stmt;
use PHPStan\Parser\Parser;

class StatementResultTest extends \PHPStan\Testing\TestCase
{

	public function dataIsAlwaysTerminating(): array
	{
		return [
			[
				'return;',
				true,
			],
			[
				'foo();',
				false,
			],
			[
				'if (true) { return; }',
				true,
			],
			[
				'if (true) { return; } else { }',
				true,
			],
			[
				'if (false) { } else { return; }',
				true,
			],
			[
				'if (false) { } else { }',
				false,
			],
			[
				'if (doFoo()) { return; } else { }',
				false,
			],
			[
				'if (doFoo()) { return; } else { return; }',
				true,
			],
			[
				'if (doFoo()) { continue; } else { break; }',
				true,
			],
			[
				'if (doFoo()) { continue; }',
				false,
			],
			[
				'if (doFoo()) { continue; } else { continue; }',
				true,
			],
			[
				'if (doFoo()) { if (true) { return; } } else { }',
				false,
			],
			[
				'if (doFoo()) { if (true) { return; } } else { return; }',
				true,
			],
			[
				'foreach ($x as $v) { return; }',
				true,
			],
			[
				'foreach ($x as $v) { break; }',
				false,
			],
			[
				'foreach ($x as $v) { continue; }',
				false,
			],
			[
				'foreach ($x as $v) { if (doFoo()) { return; } }',
				false,
			],
			[
				'foreach ($x as $v) { if (doFoo()) { return; } else { return; } }',
				true,
			],
			[
				'foreach ($x as $v) { if (true) { return; } }',
				true,
			],
			[
				'foreach ($x as $v) { if (true) { break; } }',
				false,
			],
			[
				'foreach ($x as $v) { if (true) { continue; } }',
				false,
			],
			[
				'foreach ($x as $v) { if (doFoo()) { return; } else { break; } }',
				false,
			],
			[
				'foreach ($x as $v) { if (doFoo()) { if (doBar()) { return; } else { break; } } else { break; } }',
				false,
			],
			[
				'switch ($x) { case 1: return; default: return; }',
				true,
			],
			[
				'switch ($x) { case 1: return; }',
				false,
			],
			[
				'switch ($x) { case 1: if (doFoo()) return; default: return; }',
				false,
			],
			[
				'switch ($x) { case 1: break; default: return; }',
				false,
			],
			[
				'switch ($x) { case 1: if (doFoo()) { break; } else { break; }; default: return; }',
				false,
			],
			[
				'switch ($x) { case 1: if (doFoo()) { return; } else { break; }; default: return; }',
				false,
			],
			[
				'try { return; } finally { }',
				true,
			],
			[
				'try { } finally { return; }',
				true,
			],
			[
				'try { return; } catch (Exception $e) { return; }',
				true,
			],
			[
				'try { return; } catch (Exception $e) { }',
				false,
			],
			[
				'try { break; } catch (Exception $e) { break; }',
				true,
			],
			[
				'try { break; } catch (Exception $e) { break; } catch (OtherException $e) { return; }',
				true,
			],
			[
				'try { break; } catch (Exception $e) { break; } catch (OtherException $e) { }',
				false,
			],
			[
				'while (doFoo()) { }',
				false,
			],
			[
				'while (doFoo()) { return; }',
				true,
			],
			[
				'while (doFoo()) { break; }',
				false,
			],
			[
				'do { } while (doFoo());',
				false,
			],
			[
				'do { return; } while (doFoo());',
				true,
			],
			[
				'do { break; } while (doFoo());',
				false,
			],
			[
				'for ($i = 0; $i < 5; $i++) { }',
				false,
			],
			[
				'for ($i = 0; $i < 5; $i++) { break; }',
				false,
			],
			[
				'for ($i = 0; $i < 5; $i++) { return; }',
				true,
			],
			[
				'foreach ($array as $val) { if ($val === 1) { continue; } else { throw new \Exception(); } }',
				false,
			],
			[
				'foreach ($array as $val) { if ($val === 1) { continue; } throw new \Exception(); }',
				false,
			],
			[
				'{ if ($val === 1) { continue; } throw new \Exception(); }',
				true,
			],
			[
				'throw new \Exception();',
				true,
			],
		];
	}

	/**
	 * @dataProvider dataIsAlwaysTerminating
	 * @param string $code
	 * @param bool $expectedIsAlwaysTerminating
	 */
	public function testIsAlwaysTerminating(
		string $code,
		bool $expectedIsAlwaysTerminating
	): void
	{
		/** @var Parser $parser */
		$parser = self::getContainer()->getByType(Parser::class);

		/** @var Stmt[] $stmts */
		$stmts = $parser->parseString(sprintf('<?php %s', $code));

		/** @var NodeScopeResolver $nodeScopeResolver */
		$nodeScopeResolver = self::getContainer()->getByType(NodeScopeResolver::class);
		/** @var ScopeFactory $scopeFactory */
		$scopeFactory = self::getContainer()->getByType(ScopeFactory::class);
		$result = $nodeScopeResolver->processStmtNodes(
			$stmts,
			$scopeFactory->create(ScopeContext::create('test.php')),
			static function (): void {
			}
		);
		$this->assertSame($expectedIsAlwaysTerminating, $result->isAlwaysTerminating());
	}

}
