<?php declare(strict_types = 1);

namespace App;

use App\Playground\PlaygroundClient;
use App\Playground\PlaygroundResult;
use GuzzleHttp\Promise\Utils;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class PlaygroundConcurrencyTest extends TestCase
{

	public function testRun(): void
	{
		$hashes = [
			[
				'c64505e0-5173-424a-a5da-1f8ca503997a',
				'224dba41-323a-4d45-8885-a0b697018602',
				'adf566da-efb5-46da-93d2-d13d368934ac',
				'73ab08ff-bda4-4a66-b20c-ef7fadacacd9',
			], [
				'4f86dceb-20e1-406d-92d4-eccd8da4c31b',
			], [
				'4b0bfe9f-4e01-4067-b4fb-e65c11e9da57',
			], [
				'c83773ee-3c15-48cb-8461-0845def9f2d9',
			], [
				'9e3f4300-ee94-4f03-98cf-cc5cfa329f15',
			], [
				'766659f0-7300-4297-81ff-f32314bbb9ac',
			], [
				'5a99ead8-2a1a-4421-8486-d4265729c82d',
			], [
				'34e05837-1aa9-41f2-8a74-0d3b30f7f54d',
				'65dbfe18-b20e-4655-a280-935f5089d1f3',
			], [
				'770c0b81-f547-4214-b677-dac3d3c1cc8f',
			], [
				'ae844e9f-330f-4aef-b952-94984e7c9d42',
			], [], [
				'd1f68381-095d-45c3-b89a-f9eacfa4b18b',
			], [
				'b8e9d9e3-73bd-4d8b-a041-41224ecff481',
			], [
				'cd2b0fc0-5cd7-43cf-bed8-4b1ba7785a57',
			], [
				'124786fa-fdac-4968-9647-0e04bb61f216',
			], [
				'03b14d64-3e97-49bd-a994-3c22570ebde3',
			], [
				'27d71e38-7ede-4514-899f-2038dca94390',
				'44b1e036-0aed-48e6-9a85-cb75a372b51e',
			], [
				'00941f6f-1737-4752-8850-976c0584793b',
				'186628b5-65a8-4333-9fd3-5918c8206ddd',
				'734f2d64-79e1-4973-b81f-c85f0d6dc814',
				'8bcebe9b-d999-49b2-ac15-7969ae1c0800',
			], [
				'e09f4ab5-16fa-4be9-a613-1e604986e2bf',
			], [
				'b07c57ab-5084-4149-9c8b-21c1e14d2377',
			], [
				'39bbbf31-89cb-4835-9423-3e0dcd672244',
			], [
				'd4fb1020-a6e4-42d2-9ebf-848d94cf434f',
				'23d36685-80b6-4e2c-be0e-0c7632c160b5',
			], [
				'17f00d68-0912-4f7f-84fc-6c69fa3683e3',
			], [
				'18000821-8803-43f6-b17b-abd1d49c6cce',
				'771ab3d4-54fb-4552-8c4e-9ac0633e457c',
				'fc9004dd-20d2-4ecb-a3e2-7cb9b59161f5',
			], [
				'4b5e90b0-8028-4772-a09e-98ab9de07d1a',
			], [
				'a79d1e2f-932f-4c40-8e92-530baa0a3fb8',
			], [
				'08e5a1cd-bb50-45c5-ab70-b5530b2815dd',
			], [], [
				'b93459e8-f95d-439d-b326-f03853ed13a5',
			], [
				'a374c54d-1422-4c1e-9a34-5005f6668c8c',
			], [
				'31fc807c-3553-4f8d-ba0b-248fd800fa81',
			], [
				'aae87ebf-09e9-4bd1-a13b-2a39ff9af6f3',
			], [
				'c17faf82-5d21-4283-a324-b5976c6c1414',
				'2dd6fcf6-b07d-4ea6-9958-97c7393d24f0',
			], [
				'07c01a9b-f5e8-4f48-8591-105d9d715607',
			], [
				'dc24e5cb-ca3c-45f5-a49e-7425604e7ac9',
				'71f1f248-790a-42d2-94a7-d7d732d0a453',
				'41545b41-043e-4ee6-acc5-87efebf87c26',
				'e6992178-7eb0-43fe-a4f9-91bf5f0e173e',
			], [], [
				'f462b4b3-ba01-44e1-a68a-b157e9bd8621',
				'39783e69-bcfd-426f-b9f9-48f5806e37e2',
				'9829582c-f3a5-48e4-ae43-d43d709e8282',
			], [
				'5e363ac1-aebb-4ab6-8d8c-ed2f896ee088',
				'b4bd2d03-7465-4b32-b731-895522112f57',
				'f19a1c5d-44eb-4f25-bb44-b5126fe29b0a',
			], [
				'ee543fa3-2a51-4e51-b4ff-5b04778276b5',
			], [
				'1b70ab7b-24e9-4507-bb77-c175607bf78f',
				'74a325e0-0502-44d8-b536-5b348d14ec97',
			], [
				'b4d84b76-413d-4cab-961a-ba20da3497fd',
			], [
				'c3cb9d99-758c-4111-8eff-87aeec52ead2',
			], [
				'1aa5b440-3ce7-4a8b-8713-ed5f691b88f6',
				'82d7155a-203e-410d-b07d-d79481dbc73a',
			], [
				'40b67ea9-c278-439d-9666-1a6be99caa2a',
			], [], [
				'14a1245c-505f-45f5-abfd-eb035ac95cec',
			], [
				'1b2b416b-9c2b-49bd-98a8-5630cb3f7c00',
			], [
				'1d06b068-483f-483e-a0cf-ef0ff3a29ade',
				'9e3f4300-ee94-4f03-98cf-cc5cfa329f15',
			], [
				'd13c93de-adae-4900-a9b9-b9331c13e992',
				'98e6f390-fea4-4b10-8b6a-a304181e0ffe',
			], [
				'f606a5ab-8e8d-4e2a-a04c-8ac7991f947a',
			], [
				'141cc3b7-70e1-4676-a61f-12c7cd9dbc0d',
			], [
				'54561da3-8e55-438b-91b1-cf4c73eed57f',
			], [
				'52bfec6e-2081-4959-a344-399262a7d401',
			], [
				'43bf48f4-947d-4d96-9fc1-33e25fde3adb',
			], [], [
				'bddbfc71-bae5-49a2-9d64-0472e559781b',
			], [
				'59bdd0e3-3491-4ee8-8314-d99b7e1dc04e',
			], [
				'74032d14-212e-4d02-9871-dc2e80ec2808',
			], [
				'd14466f0-f81e-4594-8ba9-8d87badb70f6',
			], [
				'055f666a-f091-4867-aafa-a5df65a75ede',
				'04a24457-2c67-4df0-8ef8-9afdc01f405f',
				'411cfcfb-8487-4aff-9ae1-44751eb8c646',
			], [
				'99bd746d-80c2-4eb1-8506-2840c6fd5089',
			], [
				'934fcc4c-109b-4696-b1f4-4586a04cbcb9',
				'766605b2-3990-42a2-a7cb-4933c51283e5',
			], [], [
				'7b6a301e-f874-4dce-bcd5-1c54cd42e854',
			], [
				'8abf5a8e-16f4-4891-aeb8-9dc8a054e20a',
			], [
				'911404cc-308a-4334-ad23-a180a5dfccb7',
			], [], [
				'85c66c6f-a6d4-4084-8815-07d3f46b099d',
			], [
				'5224db7e-74d8-4df2-9e52-035bf8f7f700',
				'2c648a14-7501-4e8d-b538-bad9100fc1e8',
			], [
				'b99b9305-2db9-4ec5-a2cc-9c8c2ddaa9f0',
			], [
				'abf2e5c9-90a3-40fc-9979-6d19255e7c67',
			], [
				'74a191d3-489d-430c-adcc-2be1bd1775da',
			], [
				'ed1af86c-a6b8-4b4c-9824-11c720e01b90',
			], [
				'ae4801c3-0f97-45b4-a40b-171e51d7ebee',
				'7693163e-7db7-47fa-950d-baf59ec3f84c',
			], [
				'c102939c-132d-44c9-a159-f93b35d83ac1',
				'26cf5f07-d39c-4d81-bbd8-80850dc57411',
				'7fb590e6-2122-4f99-9ac3-66de38d7c0b1',
			], [
				'6582e294-33e2-4507-8b65-3486e8e49535',
			], [
				'ce4a9861-014d-4ccc-84db-0e8d9ab47e34',
				'c44956e6-9bf4-4ae7-ac52-4f8c62579a38',
			], [
				'807ccb92-293e-4443-9a37-90d210e75a89',
			], [
				'7bf13ee0-c86c-448a-8d5b-2e59cb95d5ac',
				'a9895ddd-6838-4652-9c02-32d9f503bd21',
				'b9574955-b551-4ccf-8313-54e8be3dc9e0',
				'81f8b8c1-2e27-4106-a7f0-325063066d78',
				'e4bb72bb-849a-4b96-9943-39739900a4d3',
			], [
				'90231e5c-ef8c-42bf-99a7-333f9cfc3980',
				'7c18dc89-723a-4d2f-a7df-d71c91b7f48d',
				'03cf1e56-3a69-4ed2-b2d3-63667fa91230',
			], [
				'4f38a849-8c15-47aa-a6c9-b416aa4f4a64',
				'7d2c5bb4-aaae-4295-96d1-8aee8e20e6ba',
				'02b4d43a-d070-491f-8b73-cbf30591f07e',
				'c3d1789b-d5d4-4083-9bbe-efdf7e7932a9',
			], [
				'0d08bf7b-5819-412f-a890-1b9a3caf9abd',
			], [
				'671b1297-7856-4edf-871f-8f9e1d4f7169',
				'cbd2c0cb-5b13-44e8-af1d-ce3bc6984456',
				'ee4105dd-a1a1-45a8-9f44-ed1dd0cd03d9',
				'fc66daf3-efad-4edf-8a96-72dbd56e81b9',
				'b3328236-bda4-4ebb-92c3-7ada868b1d84',
			], [
				'd6bd5e2f-c8e7-4b07-b0ab-dc02940c71e4',
				'5866015d-9773-4f2b-a564-ba471fb8a2d9',
			], [
				'cf79be4b-a83a-4a68-b8ff-8fdf8e8ed49f',
				'53d3160d-0b51-4bb8-add1-e49aecc78458',
			], [
				'd19ae391-4d25-4032-8c75-e8ccc30c9d19',
			], [
				'91a14845-6307-4c27-b2d3-4e029c7f8864',
			], [
				'1d118150-3781-41f7-9924-2e0a03721a00',
			], [
				'079841f1-1961-44a4-acfa-c8a308fea2fc',
			], [
				'27ab1228-4e19-423a-a440-ed26982ac171',
				'770c0b81-f547-4214-b677-dac3d3c1cc8f',
			], [
				'9e875048-2e14-42de-b846-1dc04c67c0aa',
			], [
				'15a41cbf-20e8-409e-898f-19afcf823750',
				'38a882d5-d57b-4927-9acd-43a4a0213e99',
			], [
				'cb3fafd4-7c39-47c9-ac6a-972d110b47d5',
				'5d1bd36d-062e-400c-94ce-de7678ee4f74',
			], [
				'718a84cf-24ba-449d-b364-d8c20bc8e7ed',
				'7d472ba3-ec9e-4f08-8566-bccebced30bb',
			], [
				'7ee5959d-2f86-40af-b006-e9b30d129ca0',
				'ba0bfb07-1237-47cd-a61f-5d3bb34a6ff5',
			], [], [
				'0db2cb6e-df24-4cbe-85ec-a85edc7d5c71',
			], [
				'c4e62cc8-4e3d-4637-b060-bbba2ec52f69',
			], [
				'fbb053a0-9a30-4167-8385-cee47c509f9e',
				'b73c58e0-22df-4e54-80c4-27311e7a091b',
				'ebc7d3f9-d031-4793-8fb6-35e8564df7c1',
			], [
				'0e3b1108-a4b1-4417-a1b8-b93e47eef072',
				'74f9deeb-80bf-4f7b-a862-7a48d7816500',
			], [], [
				'd08f913e-0ad8-4fd6-9b26-fba163dddd6a',
				'14792d1b-caba-4453-bb2f-5dfdd305cc40',
			], [
				'f6cd6866-3de6-4034-8b7c-dc083bbc17c2',
				'1db5d8ae-21df-46bd-8361-818e872964c0',
			], [
				'30f1b504-2925-4b61-85f9-735d2bcc8343',
			], [
				'926a2aaf-da15-474c-8f71-c1baf24fb119',
			], [
				'5200477a-9300-454d-bca8-59511b8c51d3',
				'952f7013-2b00-4cb4-9e26-353a9924f3b3',
				'8e09eb41-fa34-4714-9996-994c911d8910',
				'8edeab32-683a-4b49-86fa-5213bd955b10',
			], [
				'98a3d03f-add8-407f-ac45-69ac58dbba7e',
				'd965bee2-214a-494f-b2ea-1fb3fe973443',
			], [
				'436cd2fb-430a-44f8-b2fc-2c75c884b41e',
			], [
				'131d81a7-ffa4-4ee5-bf1e-1028b0bd478b',
			], [
				'7f09bc66-f22a-407b-a78d-d81dc1002d71',
			], [
				'e7b2b60c-c735-4196-961c-4af764161afa',
				'9aeaac7e-b69b-41e8-9c29-78ab40f1276a',
			], [
				'd388d25d-7ccc-4f4f-89f2-7fafde83fa83',
			], [
				'c279cbdd-2f25-46ed-9579-6e5e9a546e3b',
				'b2bd8cbe-d16c-4e9f-8b25-446421aa8b6e',
				'5419cf98-36f9-470d-bbb2-329afc3d3498',
			], [
				'd3f05294-9abc-4f48-ad77-c71d371b4cfd',
				'fba7b0b0-735f-4ba5-8fc4-b33a94dcff04',
				'4b79df96-40b4-4720-b270-ac98455f80b3',
				'f9633645-6866-48d6-9f7a-b4c4bbaeb4de',
				'f3e6a76b-5f55-4940-89fb-f7f0c4a2e4e7',
				'e1bdb0e3-c668-418a-9827-37f855cd8302',
			], [
				'dae26e88-0e7c-4b0e-bb31-e58cea1de089',
			], [
				'11157c7b-bb79-4596-a635-a2b9221a2c11',
			], [
				'8f3c6888-6565-487b-a7ae-7ebbd3baee49',
			], [
				'57150eac-dde7-4f08-b05d-549720c8e5ea',
				'2ed528dc-396b-43f0-9859-1a515439cf52',
			], [
				'462f9588-3fb2-4372-94eb-f237c3819f48',
			], [
				'4d8f632c-d4fc-4cc6-b9e2-ae81db46b548',
				'0f595079-d3a0-4fd1-aa2a-af28d9068fda',
				'a87dab63-efd1-4783-8c85-89ed46b630fc',
			], [
				'04cc3e0d-5cab-417e-a9ee-41cb96bf39d7',
				'd068bb79-a0e4-4fb6-a797-867538af2091',
			], [
				'eaaf2943-60db-49fa-9336-a2dead66c788',
			], [
				'0e339d8a-da81-4edf-90f0-5f8150055d69',
			], [
				'0eededac-4378-4914-8928-ce5cb34cba85',
			], [
				'cd33a591-5f93-4df3-b878-49af85ef487e',
			], [
				'5cf62680-7651-4d08-bc82-69be0fe581aa',
			], [
				'b33506ff-66aa-45c9-8d81-db3fa4227b0b',
				'f04ed63c-88c0-4169-9ed0-97b1f92daa92',
				'bdb7b5cd-d173-47e3-a75c-1ff2b67d1a7d',
			], [
				'80db9c3d-6084-4a41-80ce-e4a6faebc24e',
			], [
				'896f13be-9ac0-43bd-9940-d53fbdb6e4be',
			], [
				'd7f7272b-92ac-4497-a6b2-07c98e3df70e',
			], [
				'2eda8bb9-48f7-4dbe-8a73-4bc32d9806d6',
			], [
				'20eca26c-c9a6-458e-ab03-23cf27cd57eb',
			], [
				'd7904a4a-44a6-4bb2-a889-cc1dde0732c7',
				'63d35fc8-acbd-4809-b4fb-c1c3556b6f85',
			], [
				'8b1afd86-ab90-47d1-b17b-530586f8edf7',
			], [
				'c03dea3a-c5d2-4fa7-85e5-2e58f5f43cd1',
				'14dff9d6-32e4-4553-bb2b-18924a60a02a',
			], [
				'a0d9b3fd-e762-4022-80b5-51223678a665',
			], [
				'd1e5e1fe-0a3c-4bcb-a5f0-eb3850ffd9f5',
				'25dff7d9-069a-4aba-b4ab-421a5f497546',
				'4734b0e3-2381-4fb7-8054-783d62d96063',
				'0981f368-426e-415c-bfd3-9e206881d81a',
			], [
				'950a06eb-8c09-4ea3-8488-91af2b8bb3a0',
			], [
				'f66b9fc9-d92f-4a7e-aa1b-34483e6cf92c',
			], [
				'2c338741-4eae-443a-b677-e832a81ca29a',
			], [
				'c32ca1b7-b1e8-42c0-9bfd-56ba031b887c',
				'6ff03363-9435-4fe4-a560-d5175e679ad5',
				'cdac300e-68c4-44d8-90bb-0357b200cc06',
				'3c686f4e-6406-4a93-a75a-8e7a941154e0',
			], [
				'899f83f5-5deb-4335-b8e7-54c4f3315abb',
			], [
				'b5055760-03d5-4cda-b799-7801404b2790',
				'9c9e69ab-d519-4f29-86be-49c41f2b668b',
			], [], [
				'73c7b029-ffbf-4b15-b6fe-19a5f6ee43d5',
				'58821a86-a033-4c9f-a36d-609a34bd9d3b',
			], [
				'8dd43f1e-0209-471b-bf39-895be1b1a9b5',
			], [
				'6b7387d8-063c-43c5-ba72-740d3a477fe8',
			], [
				'ec4cb784-4886-4bb8-83b8-e034fd042f55',
			], [
				'd716d2ac-adc4-4f4a-bdc7-2576859f90ee',
				'da18cec8-7577-4d42-b03b-8697da96438e',
			], [
				'f9cd163f-bcf1-4547-b332-264c94aebb5c',
			], [], [
				'db3495e0-2cc4-4acb-ba04-ce742258680a',
				'e2636992-cccb-46db-9f06-56a4e34c0855',
				'9f70fe22-c99a-4b12-8a7b-6dec25a26c6d',
				'74d26602-a918-47a3-8aba-1db613ddeeef',
			], [
				'1fbc38f2-b625-4a0d-b8e6-2423508a634a',
			], [
				'e7652aad-b990-4aa7-8b6e-eeed31adc036',
				'7676c51e-adc9-4b12-b72f-866417d256cf',
				'f90376e0-9612-4994-b515-0725a248e756',
				'aea94433-f31e-4d97-a668-25689ecb69d5',
				'd5cad7f0-6501-4ea8-bc80-08e7d6ea2c29',
				'bb5d6c90-4df9-4174-b16d-caee1296f944',
				'73c7b029-ffbf-4b15-b6fe-19a5f6ee43d5',
				'e52e08c5-e090-4a07-b323-7ea9521fa07f',
			], [
				'63d8e0bc-78b4-41ab-b077-5244e2e218b0',
			], [
				'172e2161-c350-4466-8867-de074489c34f',
			], [
				'19b1ac30-50b8-404b-9da8-9b1a3b6f90b1',
				'3dfa4f4f-a9c5-4d73-b29a-a98ebbfd8680',
			], [], [
				'f9336943-20db-4b3e-a9ba-671b6f58bd78',
			], [
				'ca91418e-b23e-47c2-819d-617d3ffa13f5',
				'86c6bcf5-fa24-4e55-a66d-118031ef9ea0',
			], [], [
				'0f0d4b9f-650a-4820-a7f7-783076a06be9',
			], [], [
				'de46736e-f6da-495a-ad3d-a1484626dfeb',
				'ae3b7244-1720-4bf5-8bf3-5222caf2dacf',
			], [
				'f1bfae15-4eb5-4ca4-95f9-d7fc84983274',
			], [
				'053b9b77-618e-4af2-8105-c2a572945565',
			], [], [
				'33610217-6278-4a1c-b2cc-dd9f9f837fcd',
				'917106e6-839c-43a0-aeff-a0c9153af49f',
				'a60c6dfc-901e-404d-a2f9-8c9dd5dcc30f',
			], [
				'e5e5cb47-0990-4b36-b0e1-8a46b568a80b',
			], [
				'fb9118d2-7995-4e1a-9b74-afde09457f85',
				'd6509345-6a03-4132-be7a-b3b85aa14b1f',
			], [
				'78d89125-d555-48b2-82bf-1a1f2f7f31a2',
			], [
				'a30bb64f-9723-460b-8d09-33efb3afdc40',
			], [
				'12427d46-012a-4477-b5dc-7d388fe622cc',
				'4402c484-b85d-441c-9edb-0abb055cb093',
			], [
				'07be4916-5e39-4da2-a566-8c9c1922ea89',
			], [
				'82e6d926-5634-4f25-ab61-c08cf403582b',
			], [
				'579c6091-9983-45ee-bcfa-54565c3d38be',
			], [
				'884e2c78-c95c-46f8-8a01-0d990cddee0a',
				'9b950ab9-7adf-43be-9bf5-b4aa7380b0ef',
			], [
				'75359ce2-17e0-43d5-b889-d95d1d608b78',
			], [
				'f5638662-c6d7-40c8-8f80-49fda9ceedea',
			], [
				'2c1e2424-3b4d-4a31-aca7-0e8c74633a86',
			], [
				'eae81481-04ee-44d2-8ee8-e573625aa034',
				'0d649821-d7b9-446b-99a5-acd0e3a66002',
				'd61749a7-76e5-480a-8057-cd8a1c258523',
			], [
				'608cb794-aac1-427c-bf82-7c4d1ea4dce3',
			], [
				'83e4cfa9-bd46-4af9-98b5-b9c33ddc3c45',
				'057fa3cb-2a64-43d7-af23-21b158daabde',
				'20b5bb2d-4591-4026-86d1-54e8c25a8e31',
			], [
				'c1f8afc8-7248-4e20-95da-0eeaa5e58dec',
			], [
				'34956aa9-a75c-42f8-a12f-67d1625cb327',
				'5c021762-60c2-49fe-aada-b723f2780c28',
			], [
				'db9709bd-d954-42f8-87d5-0192ed79cadf',
			], // https://phpstan.org/r/82e6d926-5634-4f25-ab61-c08cf403582b - https://github.com/phpstan/phpstan/issues/2567
			[
				'03d75586-726e-409d-8ed8-4be10d0eff8a',
				'9f3b42b2-13c0-4409-8a12-c909f81a3e77',
			], [
				'4e0d24d7-309a-4ac2-94d3-34c43fe5b36a',
				'040ec780-339d-47de-bd65-62597f5ae75e',
				'0cd987f6-4d6c-452f-ae6e-ac3a292f9504',
				'28a69405-e099-400c-954c-a0b5454d3f1a',
				'f54d1353-9c30-446a-8661-805344efa9ad',
			], [
				'73a29f4c-ae8e-4e22-bb61-c22a5d3e618c',
			], [
				'2a565ae0-7c2c-4c5f-964d-e235c775bb5b',
				'f04e6f41-9490-498a-8ed5-ce98e9529e0b',
			], [
				'721592ed-f4d1-4bb1-a7f0-047bd2183a55',
			], [
				'd6cb91f9-e428-4652-a8b0-16ee03ff6759',
			], [
				'd84045c3-7be2-44f8-aa76-55100c7163ce',
				'9f3d813c-281b-48a7-bd82-6384c06d44e7',
				'332e2d27-1152-44c6-bacb-4c0c32bb5309',
				'abcddfb0-3aca-48de-af9a-5989de3440d0',
			], [
				'8798f6a8-bbbd-4046-a314-017c580c3f71',
			], [
				'3055bffe-6a64-4060-97fc-aeb33e1e9dfd',
			], [
				'4f184387-bacb-4bda-9592-c04da6bc4d03',
			], [
				'1a0e1498-6389-49fc-8d61-6ad930f6c3bc',
			], [
				'0d08bf7b-5819-412f-a890-1b9a3caf9abd',
			], [
				'5416ebf1-03cb-4fdd-8bdb-b703d3ea81d6',
				'43b696e1-4822-41f0-a95f-503d7a56c31c',
				'3b7a8e5d-350a-46c7-a75b-1b06b239eddb',
				'a05955fc-e576-4888-a860-d1080ad7e2ca',
				'9beaff24-4170-4f27-8c8c-e4325592e1f7',
			], [
				'249d5f01-7caf-406d-bf12-5f6b116c3810',
				'282b0f74-83b1-4380-b4f3-8074f1b3e426',
				'7d2742f1-d521-4767-8723-85a7fbf26f62',
			], [
				'48fbd567-10f1-466d-94bb-fa342774b607',
			], [
				'53d70e4d-dce3-4d55-bad5-6a5d707f77b1',
			], [
				'2e37baca-9578-4a98-8b06-3480ab0ca9cc',
			], [
				'2026695c-98ae-49f9-9e3f-f88c1051c6f0',
				'c37ea4c0-1b03-4e0b-9b11-5cbdabef9e8c',
			], [
				'46bb8d5d-c037-435d-a546-0389715ab074',
			], [
				'b8dbc80d-ded7-42e4-9ed5-db6d8d795eb0',
				'9b0361b9-b4f3-43b9-821a-7e5739e28f99',
			], [
				'38dd3f24-0b99-445e-b6ec-8162fe1950ea',
			], [
				'7d71e14b-2165-4dd2-b25f-89228a4dfe8d',
			], [
				'a845d4fb-202a-4d19-b277-643905fd7f94',
			], [], [], [], [], [
				'980ff353-9660-43c5-b68f-f0e4a4bcab92',
			], [], [
				'ac700cd1-8791-482d-a7a4-5174e715cd2b',
			], [
				'87a4b338-f3bb-466f-b1fc-cbd5eecc00b5',
			], [
				'05e94e10-d69d-4f18-8a99-2db523f77dcb',
			], [], [
				'dbe44f1b-cb9d-4867-b266-61beb928fbaa',
			], [], [
				'237c0227-c613-4804-95e9-2286ae5a8556',
			], [
				'e0e5e39a-1bc4-443b-8ba3-3897310fbc72',
			], [], [], [
				'12b263b7-7c5e-429e-97cc-6d5e90d8f408',
			],
		];

		$client = new PlaygroundClient(new \GuzzleHttp\Client());
		$promiseResolver = new PromiseResolver();
		$postGenerator = new PostGenerator(new Differ(new UnifiedDiffOutputBuilder('')), 'abc123');
		foreach ($hashes as $hashGroup) {
			$promises = [];
			foreach ($hashGroup as $hash) {
				$promises[] = $client->getResultPromise($hash, 'ondrejmirtes');
			}
			$promise = Utils::all($promises)->then(function (array $results) use ($postGenerator): void {
				/** @var PlaygroundResult $result */
				foreach ($results as $result) {
					$text = $postGenerator->createText($result, []);
					if ($text === null) {
						continue;
					}

					if ($result->getHash() !== '82e6d926-5634-4f25-ab61-c08cf403582b') {
						continue;
					}

					$this->fail();
				}
			});
			$promiseResolver->push($promise, count($promises));
		}

		$promiseResolver->flush();
	}

}
